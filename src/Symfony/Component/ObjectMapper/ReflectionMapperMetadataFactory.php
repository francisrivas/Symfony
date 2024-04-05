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

/**
 * @internal
 *
 * @author Antoine Bluchet <soyuka@gmail.com>
 */
final class ReflectionMapperMetadataFactory implements MapperMetadataFactoryInterface
{
    public function create(object $object): array
    {
        $refl = new \ReflectionClass($object);
        $mapTo = [];
        foreach ($refl->getAttributes(Map::class) as $mapAttribute) {
            $mapTo[] = $mapAttribute->newInstance();
        }

        return $mapTo;
    }
}
