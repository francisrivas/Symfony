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
 * Factory to create Mapper metadata.
 *
 * @internal
 *
 * @author Antoine Bluchet <soyuka@gmail.com>
 */
interface MapperMetadataFactoryInterface
{
    /**
     * @return Map[]
     */
    public function create(object $object): array;
}
