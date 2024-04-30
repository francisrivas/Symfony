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

use Symfony\Component\ObjectMapper\Attributes\Map;
use Symfony\Component\ObjectMapper\Exception\MappingException;
use Symfony\Component\ObjectMapper\Exception\MappingTransformException;
use Symfony\Component\ObjectMapper\Exception\ReflectionException;
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

    public function map(object $object, object|string|null $to = null): object
    {
        static $objectMap = null;
        $objectMapInitialized = false;

        if (null === $objectMap) {
            $objectMap = new \SplObjectStorage();
            $objectMapInitialized = true;
        }

        try {
            $refl = new \ReflectionClass($object);
        } catch (\ReflectionException $e) {
            throw new ReflectionException($e->getMessage(), $e->getCode(), $e);
        }

        $metadata = $this->metadataFactory->create($object);
        $map = $this->getMapTo($metadata, null, $object);
        $to ??= $map?->to;

        if (!(\is_string($to) && class_exists($to)) && !\is_object($to)) {
            throw new MappingException(sprintf('Attribute of type "%s" expected on "%s.', Map::class, $refl->getName()));
        }

        $mappingToObject = \is_object($to);
        try {
            $toRefl = new \ReflectionClass($to);
        } catch (\ReflectionException $e) {
            throw new ReflectionException($e->getMessage(), $e->getCode(), $e);
        }

        $mapped = $mappingToObject ? $to : $toRefl->newInstanceWithoutConstructor();
        if ($map && $map->transform) {
            $mapped = $this->applyTransforms($map, $mapped, $mapped);

            if (!\is_object($mapped)) {
                throw new MappingTransformException('Can not map to a non-object.');
            }
        }

        if (!is_a($mapped, $toRefl->getName(), false)) {
            throw new MappingException(sprintf('Expected the mapped object to be an instance of "%s".', $mappingToObject ? $to::class : $to));
        }

        $objectMap[$object] = $mapped;

        $arguments = [];
        $constructor = $toRefl->getConstructor();
        foreach ($constructor?->getParameters() ?? [] as $parameter) {
            $parameterName = $parameter->getName();
            if (!$toRefl->hasProperty($parameterName)) {
                continue;
            }

            $property = $toRefl->getProperty($parameterName);

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
            foreach ($property->getAttributes(Map::class) as $attr) {
                $map = $attr->newInstance();
                $if = $map->if;

                if (false === $if) {
                    continue 2;
                }

                if ($if && ($fn = $this->getCallable($if)) && !$this->call($fn, null, $object)) {
                    continue 2;
                }

                break;
            }

            $mapToProperty = $map?->to ?? $propertyName;
            if (!$mapToProperty || !$toRefl->hasProperty($mapToProperty)) {
                continue;
            }

            $value = $this->propertyAccessor ? $this->propertyAccessor->getValue($object, $propertyName) : $object->{$propertyName};
            if ($map && $map->transform) {
                $value = $this->applyTransforms($map, $value, $object);
            }

            if (
                \is_object($value)
                && ($innerMetadata = $this->metadataFactory->create($value))
                && ($mapTo = $this->getMapTo($innerMetadata, $value, $object))
                && (\is_string($mapTo->to) && class_exists($mapTo->to))
            ) {
                $value = $this->applyTransforms($mapTo, $value, $object);

                if ($value === $object) {
                    $value = $mapped;
                } elseif ($objectMap->contains($value)) {
                    $value = $objectMap[$value];
                } else {
                    $value = $this->map($value, $mapTo->to);
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
     * @param Map[] $metadata
     */
    private function getMapTo(array $metadata, mixed $value, object $object): ?Map
    {
        $mapTo = null;
        foreach ($metadata as $mapAttribute) {
            if (($if = $mapAttribute->if) && ($fn = $this->getCallable($if)) && !$this->call($fn, $value, $object)) {
                continue;
            }

            $mapTo = $mapAttribute;
        }

        return $mapTo;
    }

    private function applyTransforms(Map $map, mixed $value, object $object): mixed
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
