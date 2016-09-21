<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Csrf\TokenStorage;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Checks the request's attributes for a CookieTokenStorage instance. If one is
 * found, the cookies representing the storage's changeset are appended to the
 * response headers.
 *
 * TODO where to put this class?
 *
 * @author Oliver Hoff <oliver@hofff.com>
 */
class CookieTokenStorageListener
{
    /**
     * @var string
     */
    private $tokenStorageKey;

    /**
     * @param string|null $tokenStorageKey
     */
    public function __construct($tokenStorageKey = null)
    {
        // TODO should this class get its own DEFAULT_TOKEN_STORAGE_KEY constant?
        // if no, where should the sole constant be put?
        $this->tokenStorageKey = $tokenStorageKey === null ? RequestStackTokenStorage::DEFAULT_TOKEN_STORAGE_KEY : $tokenStorageKey;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $storage = $event->getRequest()->attributes->get($this->tokenStorageKey);
        if (!$storage instanceof CookieTokenStorage) {
            return;
        }

        $headers = $event->getResponse()->headers;
        foreach ($storage->createCookies() as $cookie) {
            $headers->setCookie($cookie);
        }
    }
}
