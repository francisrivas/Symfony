<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Tests\Fixtures\ClassMap\Valid;

use Symfony\Component\DependencyInjection\Attribute\WithKey;

#[WithKey('foo-attribute')]
class Foo implements FooInterface
{
    public const key = 'foo-const';
    public static $key = 'foo-prop';

    public static function key()
    {
        return 'foo-method';
    }
}
