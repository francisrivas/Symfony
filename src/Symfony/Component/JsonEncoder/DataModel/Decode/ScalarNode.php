<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\JsonEncoder\DataModel\Decode;

use Symfony\Component\TypeInfo\Type\BackedEnumType;
use Symfony\Component\TypeInfo\Type\BuiltinType;

/**
 * Represents a scalar in the data model graph representation.
 *
 * Scalars are the leaves of the data model tree.
 *
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 */
final readonly class ScalarNode implements DataModelNodeInterface
{
    public function __construct(
        public BuiltinType|BackedEnumType $type,
    ) {
    }

    public function getIdentifier(): string
    {
        return (string) $this->type;
    }

    public function getType(): BuiltinType|BackedEnumType
    {
        return $this->type;
    }
}
