<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\TypeInfo\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;

class TypeTest extends TestCase
{
    public function testTraverse()
    {
        $this->assertEquals([Type::int()], [...Type::int()->traverse()]);
        $this->assertEquals(
            [Type::int(), Type::string(), Type::intersection(Type::int(), Type::string())],
            [...Type::intersection(Type::int(), Type::string())->traverse()],
        );
        $this->assertEquals(
            [Type::int(), Type::string(), Type::union(Type::int(), Type::string())],
            [...Type::union(Type::int(), Type::string())->traverse()],
        );
        $this->assertEquals(
            [Type::int(), Type::collection(Type::int())],
            [...Type::collection(Type::int())->traverse()],
        );
        $this->assertEquals(
            [Type::int(), Type::generic(Type::int(), Type::string())],
            [...Type::generic(Type::int(), Type::string())->traverse()],
        );
        $this->assertEquals([
            Type::bool(),
            Type::float(),
            Type::collection(Type::float()),
            Type::intersection(Type::bool(), Type::collection(Type::float())),
            Type::int(),
            Type::generic(Type::int(), Type::string()),
            Type::union(Type::generic(Type::int(), Type::string()), Type::intersection(Type::bool(), Type::collection(Type::float()))),
        ],
        [...Type::union(Type::generic(Type::int(), Type::string()), Type::intersection(Type::bool(), Type::collection(Type::float())))->traverse()]);
    }

    public function testIsATypeIdentifier()
    {
        $this->assertTrue(Type::int()->isA(TypeIdentifier::INT));
        $this->assertTrue(Type::intersection(Type::int(), Type::string())->isA(TypeIdentifier::INT));
        $this->assertTrue(Type::union(Type::int(), Type::string())->isA(TypeIdentifier::INT));
        $this->assertTrue(Type::collection(Type::int())->isA(TypeIdentifier::INT));
        $this->assertTrue(Type::generic(Type::int(), Type::string())->isA(TypeIdentifier::INT));

        $this->assertFalse(Type::string()->isA(TypeIdentifier::INT));
    }

    public function testIsAClass()
    {
        $this->assertTrue(Type::object(\Traversable::class)->isA(\Traversable::class));
        $this->assertTrue(Type::object(\ArrayIterator::class)->isA(\Traversable::class));
        $this->assertTrue(Type::intersection(Type::object(\Traversable::class), Type::object(\Stringable::class))->isA(\Traversable::class));
        $this->assertTrue(Type::union(Type::object(\ArrayIterator::class), Type::array())->isA(\Traversable::class));
        $this->assertTrue(Type::collection(Type::object(\Traversable::class))->isA(\Traversable::class));
        $this->assertTrue(Type::generic(Type::object(\ArrayIterator::class), Type::string())->isA(\Traversable::class));

        $this->assertFalse(Type::object(\Stringable::class)->isA(\Traversable::class));
    }

    public function testIsNullable()
    {
        $this->assertTrue(Type::null()->isNullable());
        $this->assertTrue(Type::mixed()->isNullable());
        $this->assertTrue(Type::intersection(Type::int(), Type::mixed())->isNullable());
        $this->assertTrue(Type::union(Type::int(), Type::null())->isNullable());
        $this->assertTrue(Type::collection(Type::mixed())->isNullable());
        $this->assertTrue(Type::generic(Type::null(), Type::string())->isNullable());

        $this->assertFalse(Type::int()->isNullable());
    }

    public function testIsScalar()
    {
        $this->assertTrue(Type::int()->isScalar());
        $this->assertTrue(Type::float()->isScalar());
        $this->assertTrue(Type::string()->isScalar());
        $this->assertTrue(Type::bool()->isScalar());
        $this->assertTrue(Type::true()->isScalar());
        $this->assertTrue(Type::false()->isScalar());
        $this->assertTrue(Type::intersection(Type::int(), Type::mixed())->isScalar());
        $this->assertTrue(Type::union(Type::int(), Type::null())->isScalar());
        $this->assertTrue(Type::collection(Type::string())->isScalar());
        $this->assertTrue(Type::generic(Type::string(), Type::object())->isScalar());

        $this->assertFalse(Type::array()->isScalar());
        $this->assertFalse(Type::callable()->isScalar());
        $this->assertFalse(Type::iterable()->isScalar());
        $this->assertFalse(Type::mixed()->isScalar());
        $this->assertFalse(Type::null()->isScalar());
        $this->assertFalse(Type::object()->isScalar());
        $this->assertFalse(Type::resource()->isScalar());
        $this->assertFalse(Type::never()->isScalar());
        $this->assertFalse(Type::void()->isScalar());
    }
}
