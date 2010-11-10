<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\HttpKernel;

use Symfony\Component\HttpKernel\ResponseListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterDoesNothingForSubRequests()
    {
        $event = new Event(null, 'core.response', array('request_type' => HttpKernelInterface::SUB_REQUEST));
        $this->getDispatcher()->filter($event, $response = new Response('foo'));

        $this->assertEquals(array(), $response->headers->all());
    }

    public function testFilterDoesNothingIfContentTypeIsSet()
    {
        $event = new Event(null, 'core.response', array('request_type' => HttpKernelInterface::MASTER_REQUEST));
        $response = new Response('foo');
        $response->headers->set('Content-Type', 'text/plain');
        $this->getDispatcher()->filter($event, $response);

        $this->assertEquals(array('content-type' => array('text/plain')), $response->headers->all());
    }

    public function testFilterDoesNothingIfRequestFormatIsNotDefined()
    {
        $event = new Event(null, 'core.response', array('request_type' => HttpKernelInterface::MASTER_REQUEST, 'request' => Request::create('/')));
        $this->getDispatcher()->filter($event, $response = new Response('foo'));

        $this->assertEquals(array(), $response->headers->all());
    }

    public function testFilterSetContentType()
    {
        $request = Request::create('/');
        $request->setRequestFormat('json');
        $event = new Event(null, 'core.response', array('request_type' => HttpKernelInterface::MASTER_REQUEST, 'request' => $request));
        $this->getDispatcher()->filter($event, $response = new Response('foo'));

        $this->assertEquals(array('content-type' => array('application/json')), $response->headers->all());
    }

    protected function getDispatcher()
    {
        $dispatcher = new EventDispatcher();
        $listener = new ResponseListener();
        $listener->register($dispatcher);

        return $dispatcher;
    }
}
