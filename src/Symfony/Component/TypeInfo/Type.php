<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\TypeInfo;

use Symfony\Component\TypeInfo\Type\CompositeTypeInterface;
use Symfony\Component\TypeInfo\Type\WrappingTypeInterface;

/**
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @experimental
 */
abstract class Type implements \Stringable
{
    use TypeFactoryTrait;

    /**
     * Traverses and yields the whole type inheritance graph.
     *
     * For example traversing "string|\Traversable<int>" will yield
     * - string
     * - int
     * - \Traversable<int>
     * - string|\Traversable<int>
     *
     * @return iterable<self>
     */
    public function traverse(): iterable
    {
        if ($this instanceof CompositeTypeInterface) {
            foreach ($this->getTypes() as $type) {
                yield from $type->traverse();
            }
        }

        if ($this instanceof WrappingTypeInterface) {
            yield from $this->getWrappedType()->traverse();
        }

        yield $this;
    }

    /**
     * Tells if the type or one of its component (@see traverse()) is what is expected.
     *
     * @param TypeIdentifier|class-string $expected
     */
    public function isA(TypeIdentifier|string $expected): bool
    {
        foreach ($this->traverse() as $type) {
            if ($type !== $this && $type->isA($expected)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tells if the type or one of its component (@see traverse()) is nullable.
     *
     * @param TypeIdentifier|class-string $expected
     */
    public function isNullable(): bool
    {
        return $this->isA(TypeIdentifier::NULL) || $this->isA(TypeIdentifier::MIXED);
    }

    /**
     * Tells if the type or one of its component (@see traverse()) is a scalar.
     *
     * @param TypeIdentifier|class-string $expected
     */
    public function isScalar(): bool
    {
        return $this->isA(TypeIdentifier::INT)
            || $this->isA(TypeIdentifier::FLOAT)
            || $this->isA(TypeIdentifier::STRING)
            || $this->isA(TypeIdentifier::BOOL)
            || $this->isA(TypeIdentifier::TRUE)
            || $this->isA(TypeIdentifier::FALSE);
    }
}
