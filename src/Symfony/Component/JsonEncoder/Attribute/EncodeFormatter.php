<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\JsonEncoder\Attribute;

/**
 * Defines a callable that will be used to format the property data during encoding.
 *
 * The first argument of the callable is the input data.
 * It is possible to inject the configuration using a $config parameter.
 *
 * That callable must return the new data.
 *
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 *
 * @experimental
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class EncodeFormatter
{
    /**
     * @param callable(mixed, array=): mixed $formatter
     */
    public function __construct(
        public mixed $formatter,
    ) {
    }
}
