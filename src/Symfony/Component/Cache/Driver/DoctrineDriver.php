<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Cache\Driver;

use Doctrine\Common\Cache\Cache as DoctrineCacheInterface;

/**
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class DoctrineDriver implements DriverInterface
{
    /**
     * @var DoctrineCacheInterface
     */
    private $driver;

    /**
     * @param DoctrineCacheInterface $driver
     */
    public function __construct(DoctrineCacheInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return $this->driver->fetch($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple($keys)
    {
        $result = array();
        foreach ($keys as $key) {
            $value = $this->driver->fetch($key);
            if (false !== $value) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->driver->save($key, $value, $ttl ?: 0);
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->driver->save($key, $value, $ttl)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        return $this->driver->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function removeMultiple($keys)
    {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->driver->delete($key)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'doctrine';
    }
}
