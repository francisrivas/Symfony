<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\ObjectMapper\Tests\Fixtures\MapStruct;

use Symfony\Component\ObjectMapper\Attributes\Map;
use Symfony\Component\ObjectMapper\MapperMetadataFactoryInterface;

/**
 * @internal
 *
 * @author Antoine Bluchet <soyuka@gmail.com>
 */
final class MapStructMapperMetadataFactory implements MapperMetadataFactoryInterface
{
    public function __construct(private readonly string $mapper) {}

    public function create(object $object, ?string $property = null, array $context = []): array
    {
        $refl = new \ReflectionClass($this->mapper);
        $mapTo = [];
        if (!$property) {
            foreach ($refl->getAttributes(Map::class) as $mappingAttribute) {
                $map = $mappingAttribute->newInstance();
                if ($map->source === get_class($object)) {
                    $mapTo[] = $map;
                }
            }
            return $mapTo;
        }

        $method = $refl->getMethod('map');
        foreach ($method->getAttributes(Map::class) as $mappingAttribute) {
            $map = $mappingAttribute->newInstance();
            if ($map->source === $property) {
                $mapTo[] = $map;
                continue;
            }
        }

        if (!$mapTo) {
            $mapTo[] = new Map(source: $property, target: $property);
        }

        return $mapTo;
    }
}
