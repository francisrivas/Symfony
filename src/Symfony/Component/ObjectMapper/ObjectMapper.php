<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\ObjectMapper;

use Symfony\Component\ObjectMapper\Exception\MappingException;
use Symfony\Component\ObjectMapper\Exception\MappingTransformException;
use Symfony\Component\ObjectMapper\Exception\ReflectionException;
use Symfony\Component\ObjectMapper\Metadata\Mapping;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Object to object mapper.
 *
 * @implements ObjectMapperInterface<T>
 *
 * @template T of object
 *
 * @experimental
 *
 * @author Antoine Bluchet <soyuka@gmail.com>
 */
final class ObjectMapper implements ObjectMapperInterface
{
    public function __construct(
        private readonly MapperMetadataFactoryInterface $metadataFactory = new ReflectionMapperMetadataFactory(),
        private readonly ?PropertyAccessorInterface $propertyAccessor = null,
    ) {
    }

    public function map(object $source, object|string|null $target = null): object
    {
        static $objectMap = null;
        $objectMapInitialized = false;

        if (null === $objectMap) {
            $objectMap = new \SplObjectStorage();
            $objectMapInitialized = true;
        }

        try {
            $refl = new \ReflectionClass($source);
        } catch (\ReflectionException $e) {
            throw new ReflectionException($e->getMessage(), $e->getCode(), $e);
        }

        $metadata = $this->metadataFactory->create($source);
        $map = $this->getMapTarget($metadata, null, $source);
        $target ??= $map?->target;
        $mappingToObject = \is_object($target);

        if (!$target || (\is_string($target) && !class_exists($target))) {
            throw new MappingException(sprintf('Mapping target "%s" not found.', $target));
        }

        try {
            $targetRefl = new \ReflectionClass($target);
        } catch (\ReflectionException $e) {
            throw new ReflectionException($e->getMessage(), $e->getCode(), $e);
        }

        $mapped = $mappingToObject ? $target : $targetRefl->newInstanceWithoutConstructor();
        if ($map && $map->transform) {
            $mapped = $this->applyTransforms($map, $mapped, $mapped);

            if (!\is_object($mapped)) {
                throw new MappingTransformException('Can not map to a non-object.');
            }
        }

        if (!is_a($mapped, $targetRefl->getName(), false)) {
            throw new MappingException(sprintf('Expected the mapped object to be an instance of "%s".', $mappingToObject ? $target::class : $target));
        }

        $objectMap[$source] = $mapped;

        $arguments = [];
        $constructor = $targetRefl->getConstructor();
        foreach ($constructor?->getParameters() ?? [] as $parameter) {
            $parameterName = $parameter->getName();
            if (!$targetRefl->hasProperty($parameterName)) {
                continue;
            }

            $property = $targetRefl->getProperty($parameterName);

            // The mapped class was probably instantiated in a transform we can't write a readonly property
            if ($property->isReadOnly() && ($property->isInitialized($mapped) && $property->getValue($mapped))) {
                continue;
            }

            $arguments[$parameterName] = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
        }

        foreach ($refl->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $propertyName = $property->getName();
            $map = null;
            foreach ($this->metadataFactory->create($source, $propertyName) as $map) {
                $if = $map->if;

                if (false === $if) {
                    continue 2;
                }

                if ($if && ($fn = $this->getCallable($if)) && !$this->call($fn, null, $source)) {
                    continue 2;
                }

                break;
            }

            $mapToProperty = $map?->target ?? $propertyName;
            if (!$mapToProperty || !$targetRefl->hasProperty($mapToProperty)) {
                continue;
            }

            $value = $this->propertyAccessor ? $this->propertyAccessor->getValue($source, $propertyName) : $source->{$propertyName};
            if ($map && $map->transform) {
                $value = $this->applyTransforms($map, $value, $source);
            }

            if (
                \is_object($value)
                && ($innerMetadata = $this->metadataFactory->create($value))
                && ($mapTo = $this->getMapTarget($innerMetadata, $value, $source))
                && (\is_string($mapTo->target) && class_exists($mapTo->target))
            ) {
                $value = $this->applyTransforms($mapTo, $value, $source);

                if ($value === $source) {
                    $value = $mapped;
                } elseif ($objectMap->contains($value)) {
                    $value = $objectMap[$value];
                } else {
                    $value = $this->map($value, $mapTo->target);
                }
            }

            if (\array_key_exists($mapToProperty, $arguments)) {
                $arguments[$mapToProperty] = $value;
            } else {
                $this->propertyAccessor ? $this->propertyAccessor->setValue($mapped, $mapToProperty, $value) : ($mapped->{$mapToProperty} = $value);
            }
        }

        if (!$mappingToObject && $arguments) {
            try {
                $constructor?->invokeArgs($mapped, $arguments);
            } catch (\ReflectionException $e) {
                throw new ReflectionException($e->getMessage(), $e->getCode(), $e);
            }
        }

        if ($objectMapInitialized) {
            $objectMap = null;
        }

        return $mapped;
    }

    /**
     * @param callable(): mixed $fn
     */
    private function call(callable $fn, mixed $value, object $object): mixed
    {
        try {
            $refl = new \ReflectionFunction(\Closure::fromCallable($fn));
        } catch (\ReflectionException $e) {
            throw new ReflectionException($e);
        }

        $withParameters = $refl->getParameters();
        $withArgs = [$value];

        // Let's not send object if we don't need to, gives the ability to call native functions
        foreach ($withParameters as $parameter) {
            if ('object' === $parameter->getName()) {
                $withArgs['object'] = $object;
                break;
            }
        }

        return \call_user_func_array($fn, $withArgs);
    }

    private function getCallable(string|callable|null $fn = null): callable|string|null
    {
        if (!$fn || !\is_string($fn)) {
            return $fn;
        }

        return $fn;
    }

    /**
     * @param Mapping[] $metadata
     */
    private function getMapTarget(array $metadata, mixed $value, object $source): ?Mapping
    {
        $mapTo = null;
        foreach ($metadata as $mapAttribute) {
            if (($if = $mapAttribute->if) && ($fn = $this->getCallable($if)) && !$this->call($fn, $value, $source)) {
                continue;
            }

            $mapTo = $mapAttribute;
        }

        return $mapTo;
    }

    private function applyTransforms(Mapping $map, mixed $value, object $object): mixed
    {
        if (!($transforms = $map->transform)) {
            return $value;
        }

        if (\is_callable($transforms)) {
            $transforms = [$transforms];
        } elseif (!\is_array($transforms)) {
            $transforms = [$transforms];
        }

        foreach ($transforms as $transform) {
            $transform = $this->getCallable($transform);
            if (\is_callable($transform)) {
                $value = $this->call($transform, $value, $object);
            }
        }

        return $value;
    }
}
