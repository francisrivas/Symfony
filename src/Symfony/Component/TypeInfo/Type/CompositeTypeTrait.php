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

use Symfony\Component\TypeInfo\Exception\InvalidArgumentException;
use Symfony\Component\TypeInfo\Type;

/**
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @internal
 *
 * @template T of Type
 */
trait CompositeTypeTrait
{
    /**
     * @var list<T>
     */
    private readonly array $types;

    /**
     * @param list<T> $types
     */
    public function __construct(Type ...$types)
    {
        if (\count($types) < 2) {
            throw new InvalidArgumentException(\sprintf('"%s" expects at least 2 types.', self::class));
        }

        foreach ($types as $t) {
            if ($t instanceof self) {
                throw new InvalidArgumentException(\sprintf('Cannot set "%s" as a "%1$s" part.', self::class));
            }
        }

        usort($types, fn (Type $a, Type $b): int => (string) $a <=> (string) $b);
        $this->types = array_values(array_unique($types));
    }

    /**
     * @return list<T>
     */
    public function getTypes(): array
    {
        return $this->types;
    }
}
