<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Session\Storage;

use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;

/**
 * Allows session to be started by PHP and managed by Symfony2
 *
 * @author Drak <drak@zikula.org>
 */
class PhpSessionStorage extends NativeSessionStorage
{
    /**
     * Constructor.
     *
     * @param object|null $handler Must be instance of AbstractProxy or NativeSessionHandler;
     *                             implement \SessionHandlerInterface; or be null.
     * @param MetadataBag $metaBag MetadataBag.
     */
    public function __construct($handler = null, MetadataBag $metaBag = null)
    {
        $this->setMetadataBag($metaBag);
        $this->setSaveHandler($handler);
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        if ($this->started && !$this->closed) {
            return true;
        }

        $this->loadSession();
        if (!$this->saveHandler->isWrapper() && !$this->getSaveHandler()->isSessionHandlerInterface()) {
            // This condition matches only PHP 5.3 + internal save handlers
            $this->saveHandler->setActive(true);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        // clear out the bags and nothing else that may be set
        // since the purpose of this driver is to share a handler
        foreach ($this->bags as $bag) {
            $bag->clear();
        }

        // reconnect the bags to the session
        $this->loadSession();
    }
}
