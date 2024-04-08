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

use Symfony\Component\Config\Resource\SelfCheckingResourceChecker;

/**
 * ConfigCache caches arbitrary content in files on disk.
 *
 * When in debug mode, those metadata resources that implement
 * \Symfony\Component\Config\Resource\SelfCheckingResourceInterface will
 * be used to check cache freshness.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Matthias Pigulla <mp@webfactory.de>
 */
class ConfigCache extends ResourceCheckerConfigCache
{
    /**
     * @param $file     The absolute cache path
     * @param $debug    Whether debugging is enabled or not
     * @param $metaFile The absolute path to the meta file
     */
    public function __construct(
        string $file,
        private bool $debug,
        ?string $metaFile = null,
    ) {
        $checkers = [];
        if (true === $this->debug) {
            $checkers = [new SelfCheckingResourceChecker()];
        }

        parent::__construct($file, $checkers, $metaFile);
    }

    /**
     * Checks if the cache is still fresh.
     *
     * This implementation always returns true when debug is off and the
     * cache file exists.
     */
    public function isFresh(): bool
    {
        if (!$this->debug && is_file($this->getPath())) {
            return true;
        }

        return parent::isFresh();
    }
}
