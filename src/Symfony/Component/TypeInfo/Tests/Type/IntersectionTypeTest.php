<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\TypeInfo\Tests\Type;

use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Exception\InvalidArgumentException;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\IntersectionType;

class IntersectionTypeTest extends TestCase
{
    public function testCannotCreateWithOnlyOneType()
    {
        $this->expectException(InvalidArgumentException::class);
        new IntersectionType(Type::int());
    }

    public function testCannotCreateWithIntersectionTypeParts()
    {
        $this->expectException(InvalidArgumentException::class);
        new IntersectionType(Type::int(), new IntersectionType());
    }

    public function testSortTypesOnCreation()
    {
        $type = new IntersectionType(Type::int(), Type::string(), Type::bool());
        $this->assertEquals([Type::bool(), Type::int(), Type::string()], $type->getTypes());
    }

    public function testToString()
    {
        $type = new IntersectionType(Type::int(), Type::string(), Type::float());
        $this->assertSame('float&int&string', (string) $type);

        $type = new IntersectionType(Type::int(), Type::string(), Type::union(Type::float(), Type::bool()));
        $this->assertSame('(bool|float)&int&string', (string) $type);
    }
}
