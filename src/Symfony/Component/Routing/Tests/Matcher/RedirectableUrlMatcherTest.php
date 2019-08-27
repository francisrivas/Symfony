<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Tests\Matcher;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RedirectableUrlMatcherTest extends UrlMatcherTest
{
    public function testMissingTrailingSlash()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo/'));

        $matcher = $this->getUrlMatcher($coll);
        $matcher->expects($this->once())->method('redirect')->willReturn([]);
        $matcher->match('/foo');
    }

    public function testRedirectWhenNoSlashForNonSafeMethod()
    {
        $this->expectException('Symfony\Component\Routing\Exception\ResourceNotFoundException');
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo/'));

        $context = new RequestContext();
        $context->setMethod('POST');
        $matcher = $this->getUrlMatcher($coll, $context);
        $matcher->match('/foo');
    }

    public function testSchemeRedirectRedirectsToFirstScheme()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo', [], [], [], '', ['FTP', 'HTTPS']));

        $matcher = $this->getUrlMatcher($coll);
        $matcher
            ->expects($this->once())
            ->method('redirect')
            ->with('/foo', 'foo', 'ftp')
            ->willReturn(['_route' => 'foo'])
        ;
        $matcher->match('/foo');
    }

    public function testNoSchemaRedirectIfOneOfMultipleSchemesMatches()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo', [], [], [], '', ['https', 'http']));

        $matcher = $this->getUrlMatcher($coll);
        $matcher
            ->expects($this->never())
            ->method('redirect');
        $matcher->match('/foo');
    }

    public function testSchemeRedirectWithParams()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo/{bar}', [], [], [], '', ['https']));

        $matcher = $this->getUrlMatcher($coll);
        $matcher
            ->expects($this->once())
            ->method('redirect')
            ->with('/foo/baz', 'foo', 'https')
            ->willReturn(['redirect' => 'value'])
        ;
        $this->assertEquals(['_route' => 'foo', 'bar' => 'baz', 'redirect' => 'value'], $matcher->match('/foo/baz'));
    }

    public function testSlashRedirectWithParams()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo/{bar}/'));

        $matcher = $this->getUrlMatcher($coll);
        $matcher
            ->expects($this->once())
            ->method('redirect')
            ->with('/foo/baz/', 'foo', null)
            ->willReturn(['redirect' => 'value'])
        ;
        $this->assertEquals(['_route' => 'foo', 'bar' => 'baz', 'redirect' => 'value'], $matcher->match('/foo/baz'));
    }

    public function testRedirectPreservesUrlEncoding()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo:bar/'));

        $matcher = $this->getUrlMatcher($coll);
        $matcher->expects($this->once())->method('redirect')->with('/foo%3Abar/')->willReturn([]);
        $matcher->match('/foo%3Abar');
    }

    public function testSchemeRequirement()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo', [], [], [], '', ['https']));
        $matcher = $this->getUrlMatcher($coll, new RequestContext());
        $matcher->expects($this->once())->method('redirect')->with('/foo', 'foo', 'https')->willReturn([]);
        $this->assertSame(['_route' => 'foo'], $matcher->match('/foo'));
    }

    public function testSlashAndVerbPrecedenceWithRedirection()
    {
        $coll = new RouteCollection();
        $coll->add('a', new Route('/api/customers/{customerId}/contactpersons', [], [], [], '', [], ['post']));
        $coll->add('b', new Route('/api/customers/{customerId}/contactpersons/', [], [], [], '', [], ['get']));

        $matcher = $this->getUrlMatcher($coll);
        $expected = [
            '_route' => 'b',
            'customerId' => '123',
        ];
        $this->assertEquals($expected, $matcher->match('/api/customers/123/contactpersons/'));

        $matcher->expects($this->once())->method('redirect')->with('/api/customers/123/contactpersons/')->willReturn([]);
        $this->assertEquals($expected, $matcher->match('/api/customers/123/contactpersons'));
    }

    protected function getUrlMatcher(RouteCollection $routes, RequestContext $context = null)
    {
        return $this->getMockForAbstractClass('Symfony\Component\Routing\Matcher\RedirectableUrlMatcher', [$routes, $context ?: new RequestContext()]);
    }
}
