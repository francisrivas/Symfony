<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\TypeInfo\Type;

use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @template T of class-string
 *
 * @experimental
 */
class ObjectType extends Type
{
    /**
     * @param T $className
     */
    public function __construct(
        private readonly string $className,
    ) {
    }

    public function getTypeIdentifier(): TypeIdentifier
    {
        return TypeIdentifier::OBJECT;
    }

    /**
     * @return T
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    public function isA(TypeIdentifier|string $expected): bool
    {
        if ($expected instanceof TypeIdentifier) {
            return $expected === TypeIdentifier::OBJECT;
        }

        return is_a($this->className, $expected, allow_string: true);
    }

    public function __toString(): string
    {
        return $this->className;
    }
}
