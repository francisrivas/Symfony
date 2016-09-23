<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Cache\Adapter;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Component\Cache\Exception\InvalidArgumentException;

/**
 * Chains several adapters together.
 *
 * Cached items are fetched from the first adapter having them in its data store.
 * They are saved and deleted in all adapters at once.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class ChainAdapter implements ContextAwareAdapterInterface
{
    private $adapters = array();
    private $saveUp;

    /**
     * @param CacheItemPoolInterface[] $adapters    The ordered list of adapters used to fetch cached items
     * @param int                      $maxLifetime The max lifetime of items propagated from lower adapters to upper ones
     */
    public function __construct(array $adapters, $maxLifetime = 0)
    {
        if (!$adapters) {
            throw new InvalidArgumentException('At least one adapter must be specified.');
        }

        foreach ($adapters as $adapter) {
            if (!$adapter instanceof CacheItemPoolInterface) {
                throw new InvalidArgumentException(sprintf('The class "%s" does not implement the "%s" interface.', get_class($adapter), CacheItemPoolInterface::class));
            }

            if ($adapter instanceof AdapterInterface) {
                $this->adapters[] = $adapter;
            } else {
                $this->adapters[] = new ProxyAdapter($adapter);
            }
        }

        $this->saveUp = \Closure::bind(
            function ($adapter, $item) use ($maxLifetime) {
                $origDefaultLifetime = $item->defaultLifetime;

                if (0 < $maxLifetime && ($origDefaultLifetime <= 0 || $maxLifetime < $origDefaultLifetime)) {
                    $item->defaultLifetime = $maxLifetime;
                }

                $adapter->save($item);
                $item->defaultLifetime = $origDefaultLifetime;
            },
            null,
            CacheItem::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        $saveUp = $this->saveUp;

        foreach ($this->adapters as $i => $adapter) {
            $item = $adapter->getItem($key);

            if ($item->isHit()) {
                while (0 <= --$i) {
                    $saveUp($this->adapters[$i], $item);
                }

                return $item;
            }
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = array())
    {
        return $this->generateItems($this->adapters[0]->getItems($keys), 0);
    }

    private function generateItems($items, $adapterIndex)
    {
        $missing = array();
        $nextAdapterIndex = $adapterIndex + 1;
        $nextAdapter = isset($this->adapters[$nextAdapterIndex]) ? $this->adapters[$nextAdapterIndex] : null;

        foreach ($items as $k => $item) {
            if (!$nextAdapter || $item->isHit()) {
                yield $k => $item;
            } else {
                $missing[] = $k;
            }
        }

        if ($missing) {
            $saveUp = $this->saveUp;
            $adapter = $this->adapters[$adapterIndex];
            $items = $this->generateItems($nextAdapter->getItems($missing), $nextAdapterIndex);

            foreach ($items as $k => $item) {
                if ($item->isHit()) {
                    $saveUp($adapter, $item);
                }

                yield $k => $item;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->hasItem($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $cleared = true;

        foreach ($this->adapters as $adapter) {
            $cleared = $adapter->clear() && $cleared;
        }

        return $cleared;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        $deleted = true;

        foreach ($this->adapters as $adapter) {
            $deleted = $adapter->deleteItem($key) && $deleted;
        }

        return $deleted;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        $deleted = true;

        foreach ($this->adapters as $adapter) {
            $deleted = $adapter->deleteItems($keys) && $deleted;
        }

        return $deleted;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        $saved = true;

        foreach ($this->adapters as $adapter) {
            $saved = $adapter->save($item) && $saved;
        }

        return $saved;
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        $saved = true;

        foreach ($this->adapters as $adapter) {
            $saved = $adapter->saveDeferred($item) && $saved;
        }

        return $saved;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $committed = true;

        foreach ($this->adapters as $adapter) {
            $committed = $adapter->commit() && $committed;
        }

        return $committed;
    }

    /**
     * {@inheritdoc}
     */
    public function withContext($context)
    {
        $fork = clone $this;
        $fork->adapters = array();

        foreach ($this->adapters as $adapter) {
            if (!$adapter instanceof ContextAwareAdapterInterface) {
                throw new CacheException(sprintf('%s does not implement ContextAwareAdapterInterface.', get_class($adapter)));
            }
            $fork->adapters[] = $adapter->withContext($context);
        }

        return $fork;
    }
}
