<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Http\Tests\Logout;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\CookieClearingLogoutHandler;

/**
 * @group legacy
 */
class CookieClearingLogoutHandlerTest extends TestCase
{
    public function testLogout()
    {
        $request = new Request();
        $response = new Response();
        $token = $this->createMock(TokenInterface::class);

        $handler = new CookieClearingLogoutHandler(['foo' => ['path' => '/foo', 'domain' => 'foo.foo', 'secure' => true, 'samesite' => Cookie::SAMESITE_STRICT], 'foo2' => ['path' => null, 'domain' => null]]);

        $cookies = $response->headers->getCookies();
        $this->assertCount(0, $cookies);

        $handler->logout($request, $response, $token);

        $cookies = $response->headers->getCookies(ResponseHeaderBag::COOKIES_ARRAY);
        $this->assertCount(2, $cookies);

        $cookie = $cookies['foo.foo']['/foo']['foo'];
        $this->assertEquals('foo', $cookie->getName());
        $this->assertEquals('/foo', $cookie->getPath());
        $this->assertEquals('foo.foo', $cookie->getDomain());
        $this->assertEquals(Cookie::SAMESITE_STRICT, $cookie->getSameSite());
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isCleared());

        $cookie = $cookies['']['/']['foo2'];
        $this->assertStringStartsWith('foo2', $cookie->getName());
        $this->assertEquals('/', $cookie->getPath());
        $this->assertNull($cookie->getDomain());
        $this->assertNull($cookie->getSameSite());
        $this->assertFalse($cookie->isSecure());
        $this->assertTrue($cookie->isCleared());
    }
}
