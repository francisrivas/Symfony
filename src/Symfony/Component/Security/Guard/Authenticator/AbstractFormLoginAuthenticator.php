<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Guard\Authenticator;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * A base class to make form login authentication easier!
 *
 * @author Ryan Weaver <ryan@knpuniversity.com>
 */
abstract class AbstractFormLoginAuthenticator extends AbstractGuardAuthenticator
{
    use TargetPathTrait;

    /**
     * Return the URL to the login page.
     */
    abstract protected function getLoginUrl(): string;

    /**
     * Override to change what happens after a bad username/password is submitted.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        if ($request->getSession() instanceof SessionInterface) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }

        $url = $this->getLoginUrl();

        return new RedirectResponse($url);
    }

    public function supportsRememberMe()
    {
        return true;
    }

    /**
     * Override to control what happens when the user hits a secure page
     * but isn't logged in yet.
     */
    public function start(Request $request, AuthenticationException $authException = null): RedirectResponse
    {
        $url = $this->getLoginUrl();

        return new RedirectResponse($url);
    }
}
