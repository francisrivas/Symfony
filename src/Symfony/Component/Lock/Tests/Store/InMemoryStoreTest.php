<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Lock\Tests\Store;

use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\InMemoryStore;

/**

 */
class InMemoryStoreTest extends AbstractStoreTest
{
    use SharedLockStoreTestTrait;

    /**
     * {@inheritdoc}
     */
    public function getStore(): PersistingStoreInterface
    {
        return new InMemoryStore();
    }
}
