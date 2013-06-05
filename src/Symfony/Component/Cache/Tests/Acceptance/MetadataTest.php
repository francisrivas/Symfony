<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Cache\Tests\Acceptance;

use Symfony\Component\Cache\Cache;
use Symfony\Component\Cache\Data\CachedItem;
use Symfony\Component\Cache\Data\FreshItem;
use Symfony\Component\Cache\Data\Metadata;

class MetadataTest extends \PHPUnit_Framework_TestCase
{
    /** @dataProvider Symfony\Component\Cache\Tests\Acceptance\DataProvider::provideCaches */
    public function testWhenIStoreAnItemWithMetadataIFetchThem(Cache $cache)
    {
        $item = new FreshItem('key', 'data');
        $item->metadata->set('metakey1', 'metadata1');
        $item->metadata->set('metakey2', 'metadata2');

        $cache->set($item);
        $fetchedItem = $cache->get('key');

        $this->assertTrue($fetchedItem instanceof CachedItem);
        $this->assertTrue($fetchedItem->metadata instanceof Metadata);
        $this->assertEquals('metadata1',  $fetchedItem->metadata->get('metakey1'));
        $this->assertEquals('metadata2',  $fetchedItem->metadata->get('metakey2'));
    }
}
