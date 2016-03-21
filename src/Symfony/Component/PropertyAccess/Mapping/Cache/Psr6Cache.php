<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\PropertyAccess\Mapping\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\PropertyAccess\Mapping\ClassMetadata;

/**
 * PSR-6 adapter.
 *
 * @author Luis Ramón López <lrlopez@gmail.com>
 */
class Psr6Cache implements CacheInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    public function __construct(CacheItemPoolInterface $cacheItemPool)
    {
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * {@inheritdoc}
     */
    public function has($class)
    {
        return $this->cacheItemPool->hasItem($this->escapeClassName($class));
    }

    /**
     * {@inheritdoc}
     */
    public function read($class)
    {
        $item = $this->cacheItemPool->getItem($this->escapeClassName($class));

        if (!$item->isHit()) {
            return false;
        }

        return $item->get();
    }

    /**
     * {@inheritdoc}
     */
    public function write(ClassMetadata $metadata)
    {
        $item = $this->cacheItemPool->getItem($this->escapeClassName($metadata->getName()));
        $item->set($metadata);

        $this->cacheItemPool->save($item);
    }

    /**
     * Replaces backslashes by dots in a class name.
     *
     * @param string $class
     *
     * @return string
     */
    private function escapeClassName($class)
    {
        return str_replace('\\', '.', $class);
    }
}
