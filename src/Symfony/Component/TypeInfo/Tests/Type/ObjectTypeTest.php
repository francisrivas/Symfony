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
use Symfony\Component\TypeInfo\Type\ObjectType;

class ObjectTypeTest extends TestCase
{
    public function testToString()
    {
        $this->assertSame(self::class, (string) new ObjectType(self::class));
    }
}
