<?php

namespace Symfony\Serializer\Normalizer;

use Symfony\Component\Serializer\Tests\Fixtures\CustomNormalizer\FullTypeHints\ComplexTypesPublicProperties;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Exception\DenormalizingUnionFailedException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;

class Symfony_Component_Serializer_Tests_Fixtures_CustomNormalizer_FullTypeHints_ComplexTypesPublicProperties implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    private null|NormalizerInterface $normalizer = NULL;
    private null|DenormalizerInterface $denormalizer = NULL;

    public function getSupportedTypes(?string $format): array
    {
        return [ComplexTypesPublicProperties::class => true];
    }

    public function supportsNormalization(mixed $data, ?string $format = NULL, array $context = []): bool
    {
        return $data instanceof ComplexTypesPublicProperties;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = NULL, array $context = []): bool
    {
        return $type === ComplexTypesPublicProperties::class;
    }

    /**
     * @param ComplexTypesPublicProperties $object
     */
    public function normalize(mixed $object, ?string $format = NULL, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return [
            'simple' => $this->normalizeChild($object->simple, $format, $context, false),
            'array' => $this->normalizeChild($object->array, $format, $context, true),
            'union' => $this->normalizeChild($object->union, $format, $context, false),
            'nested' => $this->normalizeChild($object->nested, $format, $context, false),
            'unionArray' => $this->normalizeChild($object->unionArray, $format, $context, true),
        ];
    }

    public function setNormalizer(NormalizerInterface $normalizer): void
    {
        $this->normalizer = $normalizer;
    }

    private function normalizeChild(mixed $object, ?string $format, array $context, bool $canBeIterable): mixed
    {
        if (is_scalar($object) || null === $object) {
            return $object;
        }
        
        if ($canBeIterable === true && is_iterable($object)) {
            return array_map(fn($item) => $this->normalizeChild($item, $format, $context, true), $object);
        }
        
        return $this->normalizer->normalize($object, $format, $context);
        
    }

    public function denormalize(mixed $data, string $type, ?string $format = NULL, array $context = []): mixed
    {
        
        $output = new ComplexTypesPublicProperties();
        if (array_key_exists('simple', $data)) {
            $setter0 = $this->denormalizeChild($data['simple'], \Symfony\Component\Serializer\Tests\Fixtures\CustomNormalizer\FullTypeHints\DummyObject::class, $format, $context, false);
            $output->simple = $setter0;
        }
        if (array_key_exists('array', $data)) {
            $setter1 = $this->denormalizeChild($data['array'], \Symfony\Component\Serializer\Tests\Fixtures\CustomNormalizer\FullTypeHints\DummyObject::class, $format, $context, true);
            $output->array = $setter1;
        }
        if (array_key_exists('union', $data)) {
            $exceptions = [];
        $setter2HasValue = false;
        foreach (array (  0 => 'Symfony\\Component\\Serializer\\Tests\\Fixtures\\CustomNormalizer\\FullTypeHints\\DummyObject',  1 => 'Symfony\\Component\\Serializer\\Tests\\Fixtures\\CustomNormalizer\\FullTypeHints\\SmartObject',) as $class) {
            try {
                $setter2 = $this->denormalizeChild($data['union'], $class, $format, $context, false);
                $setter2HasValue = true;
                break;
            } catch (\Throwable $e) {
                $exceptions[] = $e;
            }
        }
        if (!$setter2HasValue) {
            throw new DenormalizingUnionFailedException('Failed to denormalize key "union" of class "Symfony\Component\Serializer\Tests\Fixtures\CustomNormalizer\FullTypeHints\ComplexTypesPublicProperties".', $exceptions);
        }
        
            $output->union = $setter2;
        }
        if (array_key_exists('nested', $data)) {
            $exceptions = [];
        $setter3HasValue = false;
        foreach (array (  0 => 'Symfony\\Component\\Serializer\\Tests\\Fixtures\\CustomNormalizer\\FullTypeHints\\DummyObject',  1 => 'Symfony\\Component\\Serializer\\Tests\\Fixtures\\CustomNormalizer\\FullTypeHints\\SmartObject',) as $class) {
            try {
                $setter3 = $this->denormalizeChild($data['nested'], $class, $format, $context, false);
                $setter3HasValue = true;
                break;
            } catch (\Throwable $e) {
                $exceptions[] = $e;
            }
        }
        if (!$setter3HasValue) {
            throw new DenormalizingUnionFailedException('Failed to denormalize key "nested" of class "Symfony\Component\Serializer\Tests\Fixtures\CustomNormalizer\FullTypeHints\ComplexTypesPublicProperties".', $exceptions);
        }
        
            $output->nested = $setter3;
        }
        if (array_key_exists('unionArray', $data)) {
            $exceptions = [];
        $setter4HasValue = false;
        foreach (array (  0 => 'Symfony\\Component\\Serializer\\Tests\\Fixtures\\CustomNormalizer\\FullTypeHints\\DummyObject',  1 => 'Symfony\\Component\\Serializer\\Tests\\Fixtures\\CustomNormalizer\\FullTypeHints\\SmartObject',) as $class) {
            try {
                $setter4 = $this->denormalizeChild($data['unionArray'], $class, $format, $context, true);
                $setter4HasValue = true;
                break;
            } catch (\Throwable $e) {
                $exceptions[] = $e;
            }
        }
        if (!$setter4HasValue) {
            throw new DenormalizingUnionFailedException('Failed to denormalize key "unionArray" of class "Symfony\Component\Serializer\Tests\Fixtures\CustomNormalizer\FullTypeHints\ComplexTypesPublicProperties".', $exceptions);
        }
        
            $output->unionArray = $setter4;
        }
        
        return $output;
    }

    public function setDenormalizer(DenormalizerInterface $denormalizer): void
    {
        $this->denormalizer = $denormalizer;
    }

    private function denormalizeChild(mixed $data, string $type, ?string $format, array $context, bool $canBeIterable): mixed
    {
        if (is_scalar($data) || null === $data) {
            return $data;
        }
        
        if ($canBeIterable === true && is_iterable($data)) {
            return array_map(fn($item) => $this->denormalizeChild($item, $type, $format, $context, true), $data);
        }
        
        return $this->denormalizer->denormalize($data, $type, $format, $context);
        
    }

}
