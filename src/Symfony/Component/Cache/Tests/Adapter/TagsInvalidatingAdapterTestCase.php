<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Cache\Tests\Adapter;

use Cache\IntegrationTests\CachePoolTest;

abstract class TagsInvalidatingAdapterTestCase extends CachePoolTest
{
    /**
     * @expectedException Psr\Cache\InvalidArgumentException
     */
    public function testInvalidTag()
    {
        $pool = $this->createCachePool();
        $item = $pool->getItem('foo');
        $item->tag(':');
    }

    public function testInvalidateTags()
    {
        $pool = $this->createCachePool();

        $i0 = $pool->getItem('i0');
        $i1 = $pool->getItem('i1');
        $i2 = $pool->getItem('i2');
        $i3 = $pool->getItem('i3');
        $i4 = $pool->getItem('i4');
        $i5 = $pool->getItem('i5');
        $foo = $pool->getItem('foo');

        $pool->save($i0->tag('bar'));
        $pool->save($i1->tag('foo'));
        $pool->save($i2->tag('foo')->tag('bar'));
        $pool->save($i3->tag('foo')->tag('baz'));
        $pool->save($i4->tag('foo/bar'));
        $pool->save($i5->tag('foo/baz'));
        $pool->save($foo);

        $pool->invalidateTags('bar');

        $this->assertFalse($pool->getItem('i0')->isHit());
        $this->assertTrue($pool->getItem('i1')->isHit());
        $this->assertFalse($pool->getItem('i2')->isHit());
        $this->assertTrue($pool->getItem('i3')->isHit());
        $this->assertTrue($pool->getItem('i4')->isHit());
        $this->assertTrue($pool->getItem('i5')->isHit());
        $this->assertTrue($pool->getItem('foo')->isHit());

        $pool->save($i0->tag('bar'));
        $pool->save($i2->tag('bar'));

        $pool->invalidateTags('foo/bar');

        $this->assertTrue($pool->getItem('i0')->isHit());
        $this->assertTrue($pool->getItem('i1')->isHit());
        $this->assertTrue($pool->getItem('i2')->isHit());
        $this->assertTrue($pool->getItem('i3')->isHit());
        $this->assertFalse($pool->getItem('i4')->isHit());
        $this->assertTrue($pool->getItem('i5')->isHit());
        $this->assertTrue($pool->getItem('foo')->isHit());

        $pool->save($i4->tag('foo/bar'));

        $pool->invalidateTags('foo');

        $this->assertTrue($pool->getItem('i0')->isHit());
        $this->assertFalse($pool->getItem('i1')->isHit());
        $this->assertFalse($pool->getItem('i2')->isHit());
        $this->assertFalse($pool->getItem('i3')->isHit());
        $this->assertFalse($pool->getItem('i4')->isHit());
        $this->assertFalse($pool->getItem('i5')->isHit());
        $this->assertTrue($pool->getItem('foo')->isHit());
    }

    public function testTagsAreCleanedOnSave()
    {
        $pool = $this->createCachePool();

        $i = $pool->getItem('k');
        $pool->save($i->tag('foo'));

        $i = $pool->getItem('k');
        $pool->save($i->tag('bar'));

        $pool->invalidateTags('foo');
        $this->assertTrue($pool->getItem('k')->isHit());
    }

    public function testTagsAreCleanedOnDelete()
    {
        $pool = $this->createCachePool();

        $i = $pool->getItem('k');
        $pool->save($i->tag('foo'));
        $pool->deleteItem('k');

        $pool->save($pool->getItem('k'));
        $pool->invalidateTags('foo');

        $this->assertTrue($pool->getItem('k')->isHit());
    }
}
