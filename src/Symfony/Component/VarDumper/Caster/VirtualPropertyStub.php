<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\VarDumper\Caster;

use Symfony\Component\VarDumper\Cloner\Stub;

/**
 * Represents a virtual property.
 *
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
class VirtualPropertyStub extends Stub
{
    public ?string $propertyType;

    public function __construct(\ReflectionProperty $reflector)
    {
        if (\PHP_VERSION_ID < 80400) {
            throw new \LogicException('Virtual properties are only supported from PHP 8.4.');
        }

        $this->propertyType = $reflector->hasType() ? $reflector->getType() : null;
    }
}
