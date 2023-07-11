<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Tests\Fixtures\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Foo
{
    private mixed $foo;

    public function __construct(mixed $foo)
    {
        $this->foo = $foo;
    }
}
