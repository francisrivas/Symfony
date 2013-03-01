<?php
/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Config;

use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Config\Util\CacheFileUtils;

/**
 * ConfigCache is n (almost) backwards-compatible way of using the new
 * cache implementation classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ConfigCache implements ConfigCacheInterface
{

    private $impl;

    /**
     * Constructor.
     *
     * @param string  $file  The absolute cache path
     * @param Boolean $debug Whether debugging is enabled or not
     */
    public function __construct($file, $debug)
    {
        $factory = new DefaultConfigCacheFactory($debug);
        $this->impl = $factory->createCache($file);
    }

    public function __toString()
    {
        return $this->impl->__toString();
    }

    public function isFresh()
    {
        return $this->impl->isFresh();
    }

    public function write($content, array $metadata = null)
    {
        $this->impl->write($content, $metadata);
    }
}
