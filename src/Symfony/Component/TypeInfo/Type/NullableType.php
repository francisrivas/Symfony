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
 *
 * @template T of Type
 *
 * @extends UnionType<T|BuiltinType<TypeIdentifier::NULL>>
 * @implements WrappingTypeInterface<T>
 *
 * @experimental
 */
final class NullableType extends UnionType implements WrappingTypeInterface
{
    /**
     * @param T $type
     */
    public function __construct(
        private readonly Type $type,
    ) {
        if ($type instanceof UnionType) {
            parent::__construct(Type::null(), ...$type->getTypes());

            return;
        }

        parent::__construct(Type::null(), $type);
    }

    public function getWrappedType(): Type
    {
        return $this->type;
    }
}
