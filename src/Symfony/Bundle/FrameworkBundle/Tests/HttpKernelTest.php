<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\HttpKernel;
use Symfony\Component\EventDispatcher\EventDispatcher;

class HttpKernelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getProviderTypes
     */
    public function testHandle($type)
    {
        $request = new Request();
        $expected = new Response();

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects($this->once())
            ->method('enterScope')
            ->with($this->equalTo('request'))
        ;
        $container
            ->expects($this->once())
            ->method('leaveScope')
            ->with($this->equalTo('request'))
        ;
        $container
            ->expects($this->once())
            ->method('set')
            ->with($this->equalTo('request'), $this->equalTo($request), $this->equalTo('request'))
        ;

        $dispatcher = new EventDispatcher();
        $resolver = $this->getMock('Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface');
        $kernel = new HttpKernel($dispatcher, $container, $resolver);

        $controller = function() use($expected)
        {
            return $expected;
        };

        $resolver->expects($this->once())
            ->method('getController')
            ->with($request)
            ->will($this->returnValue($controller));
        $resolver->expects($this->once())
            ->method('getArguments')
            ->with($request, $controller)
            ->will($this->returnValue(array()));

        $actual = $kernel->handle($request, $type);

        $this->assertSame($expected, $actual, '->handle() returns the response');
    }

    /**
     * @dataProvider getProviderTypes
     */
    public function testHandleRestoresThePreviousRequestOnException($type)
    {
        $request = new Request();
        $expected = new \Exception();

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects($this->once())
            ->method('enterScope')
            ->with($this->equalTo('request'))
        ;
        $container
            ->expects($this->once())
            ->method('leaveScope')
            ->with($this->equalTo('request'))
        ;
        $container
            ->expects($this->once())
            ->method('set')
            ->with($this->equalTo('request'), $this->equalTo($request), $this->equalTo('request'))
        ;

        $dispatcher = new EventDispatcher();
        $resolver = $this->getMock('Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface');
        $kernel = new HttpKernel($dispatcher, $container, $resolver);

        $controller = function() use ($expected)
        {
            throw $expected;
        };

        $resolver->expects($this->once())
            ->method('getController')
            ->with($request)
            ->will($this->returnValue($controller));
        $resolver->expects($this->once())
            ->method('getArguments')
            ->with($request, $controller)
            ->will($this->returnValue(array()));

        try {
            $kernel->handle($request, $type);
            $this->fail('->handle() suppresses the controller exception');
        } catch (\Exception $actual) {
            $this->assertSame($expected, $actual, '->handle() throws the controller exception');
        }
    }

    public function testGenerateInternalUriHandlesNullValues()
    {
        $request = new Request();

        $router = $this->getMock('Symfony\\Component\\Routing\\RouterInterface');
        $container = $this->getMock('Symfony\\Component\\DependencyInjection\\ContainerInterface');
        $container
            ->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('router'))
            ->will($this->returnValue($router))
        ;
        $container
            ->expects($this->at('1'))
            ->method('get')
            ->with($this->equalTo('request'))
            ->will($this->returnValue($request))
        ;

        $controller = 'AController';
        $attributes = array('anAttribute' => null);
        $query = array('aQueryParam' => null);

        $expectedPath = 'none';

        $routeParameters = array('controller' => $controller, 'path' => $expectedPath, '_format' => 'html');
        $router
            ->expects($this->once())
            ->method('generate')
            ->with($this->equalTo('_internal'), $this->equalTo($routeParameters))
            ->will($this->returnValue('GENERATED_URI'))
        ;

        $dispatcher = new EventDispatcher();
        $resolver = $this->getMock('Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface');
        $kernel = new HttpKernel($dispatcher, $container, $resolver);

        $uri = $kernel->generateInternalUri($controller, $attributes, $query);
        $this->assertEquals('GENERATED_URI', $uri);
    }

    public function getProviderTypes()
    {
        return array(
            array(HttpKernelInterface::MASTER_REQUEST),
            array(HttpKernelInterface::SUB_REQUEST),
        );
    }

    public function testExceptionInSubRequestsDoesNotMangleOutputBuffers()
    {
        if (version_compare(phpversion(), "5.3.2", "<=")) {
            $this->markTestSkipped('Test fails with PHP5.3.2 due to https://bugs.php.net/bug.php?id=50563');
        }

        $request = new Request();

        $container = $this->getMock('Symfony\\Component\\DependencyInjection\\ContainerInterface');
        $container
            ->expects($this->at(0))
            ->method('getParameter')
            ->with($this->equalTo('kernel.debug'))
            ->will($this->returnValue(false))
        ;
        $container
            ->expects($this->at(1))
            ->method('has')
            ->with($this->equalTo('esi'))
            ->will($this->returnValue(false))
        ;
        $container
            ->expects($this->at(2))
            ->method('get')
            ->with($this->equalTo('request'))
            ->will($this->returnValue($request))
        ;

        $dispatcher = new EventDispatcher();
        $resolver = $this->getMock('Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface');
        $resolver->expects($this->once())
            ->method('getController')
            ->will($this->returnValue(function () {
                ob_start();
                echo 'bar';
                throw new \RuntimeException();
            }));
        $resolver->expects($this->once())
            ->method('getArguments')
            ->will($this->returnValue(array()));

        $kernel = new HttpKernel($dispatcher, $container, $resolver);

        // simulate a main request with output buffering
        ob_start();
        echo 'Foo';

        // simulate a sub-request with output buffering and an exception
        $kernel->render('/');

        $this->assertEquals('Foo', ob_get_clean());
    }

    public function testRenderAttributes()
    {
        $dispatcher = new EventDispatcher();
        $resolver = $this->getMock('Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface');

        $phpunit = $this;
        $esi = $this->getMock('Symfony\\Component\\HttpKernel\\HttpCache\\Esi');
        $esi
            ->expects($this->once())
            ->method('hasSurrogateEsiCapability')
            ->will($this->returnValue(true))
        ;
        $esi
            ->expects($this->once())
            ->method('renderIncludeTag')
            ->will($this->returnCallback(function ($uri) use ($phpunit) {
                $phpunit->assertEquals('foo[bar]=foo', urldecode($uri));

                return 'foo';
            }))
        ;

        $router = $this->getMock('Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface');
        $router
            ->expects($this->once())
            ->method('generate')
            ->will($this->returnCallback(function ($name, $options) {
                return $options['path'];
            }))
        ;

        $request = new Request();

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->at(2))->method('get')->with($this->equalTo('esi'))->will($this->returnValue($esi));
        $container->expects($this->at(3))->method('get')->with($this->equalTo('request'))->will($this->returnValue($request));
        $container->expects($this->at(4))->method('get')->with($this->equalTo('router'))->will($this->returnValue($router));
        $container->expects($this->at(5))->method('get')->with($this->equalTo('request'))->will($this->returnValue($request));
        $container->expects($this->at(6))->method('get')->with($this->equalTo('esi'))->will($this->returnValue($esi));

        $container
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo('esi'))
            ->will($this->returnValue(true))
        ;

        $kernel = new HttpKernel($dispatcher, $container, $resolver);

        $content = $kernel->render('foo_controller', array(
            'standalone' => true,
            'attributes' => array('foo' => array('bar' => 'foo')),
        ));

        // if this assertion fails, it means that the assertion in 'returnCallback' failed
        $this->assertEquals('foo', $content);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRenderOnlyAllowScalarAttributes()
    {
        $dispatcher = new EventDispatcher();
        $resolver = $this->getMock('Symfony\\Component\\HttpKernel\\Controller\\ControllerResolverInterface');
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $kernel = new HttpKernel($dispatcher, $container, $resolver);

        $kernel->render('/', array('attributes' => array('foo' => array('bar' => new \stdClass()))));
    }
}
