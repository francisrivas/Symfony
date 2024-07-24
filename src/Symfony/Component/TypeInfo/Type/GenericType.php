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
 * Represents a generic type, which is a type that holds variable parts.
 *
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @template T of BuiltinType<TypeIdentifier::ARRAY>|BuiltinType<TypeIdentifier::ITERABLE>|ObjectType
 *
 * @implements WrappingTypeInterface<T>
 *
 * @experimental
 */
final class GenericType extends Type implements WrappingTypeInterface
{
    /**
     * @var list<Type>
     */
    private readonly array $variableTypes;

    /**
     * @param T $type
     */
    public function __construct(
        private readonly BuiltinType|ObjectType $type,
        Type ...$variableTypes,
    ) {
        $this->variableTypes = $variableTypes;
    }

    public function getWrappedType(): Type
    {
        return $this->type;
    }

    /**
     * @return list<Type>
     */
    public function getVariableTypes(): array
    {
        return $this->variableTypes;
    }

    public function __toString(): string
    {
        $typeString = (string) $this->type;

        $variableTypesString = '';
        $glue = '';
        foreach ($this->variableTypes as $t) {
            $variableTypesString .= $glue.((string) $t);
            $glue = ',';
        }

        return $typeString.'<'.$variableTypesString.'>';
    }
}
