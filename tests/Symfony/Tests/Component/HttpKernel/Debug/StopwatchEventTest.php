<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\HttpKernel\Debug;

use Symfony\Component\HttpKernel\Debug\StopwatchEvent;

/**
 * StopwatchEventTest
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class StopwatchEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetOrigin()
    {
        $event = new StopwatchEvent(12);
        $this->assertEquals(12, $event->getOrigin());
    }

    public function testGetCategory()
    {
        $event = new StopwatchEvent(microtime(true) * 1000);
        $this->assertEquals('default', $event->getCategory());

        $event = new StopwatchEvent(microtime(true) * 1000, 'cat');
        $this->assertEquals('cat', $event->getCategory());
    }

    public function testGetPeriods()
    {
        $event = new StopwatchEvent(microtime(true) * 1000);
        $this->assertEquals(array(), $event->getPeriods());

        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        $event->stop();
        $this->assertCount(1, $event->getPeriods());

        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        $event->stop();
        $event->start();
        $event->stop();
        $this->assertCount(2, $event->getPeriods());
    }

    public function testLap()
    {
        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        $event->lap();
        $event->stop();
        $this->assertCount(2, $event->getPeriods());
    }

    public function testTotalTime()
    {
        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        usleep(10000);
        $event->stop();
        $total = $event->getTotalTime();
        $this->assertTrue($total >= 9 && $total <= 20);

        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        usleep(10000);
        $event->stop();
        $event->start();
        usleep(10000);
        $event->stop();
        $total = $event->getTotalTime();
        $this->assertTrue($total >= 18 && $total <= 30);
    }

    /**
     * @expectedException \LogicException
     */
    public function testStopWithoutStart()
    {
        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->stop();
    }

    public function testEnsureStopped()
    {
        // this also test overlap between two periods
        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        usleep(10000);
        $event->start();
        usleep(10000);
        $event->ensureStopped();
        $total = $event->getTotalTime();
        $this->assertTrue($total >= 27 && $total <= 40);
    }

    public function testStartTime()
    {
        $event = new StopwatchEvent(microtime(true) * 1000);
        $this->assertTrue($event->getStartTime() < 0.5);

        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        $event->stop();
        $this->assertTrue($event->getStartTime() < 1);

        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        usleep(10000);
        $event->stop();
        $start = $event->getStartTime();
        $this->assertTrue($start >= 0 && $start <= 20);
    }

    public function testEndTime()
    {
        $event = new StopwatchEvent(microtime(true) * 1000);
        $this->assertEquals(0, $event->getEndTime());

        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        $this->assertEquals(0, $event->getEndTime());

        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        usleep(10000);
        $event->stop();
        $event->start();
        usleep(10000);
        $event->stop();
        $end = $event->getEndTime();
        $this->assertTrue($end >= 18 && $end <= 30);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidOriginThrowsAnException()
    {
        new StopwatchEvent("abc");
    }

    public function testSetOrigin()
    {
        $event = $this
            ->getMockBuilder('Symfony\\Component\\HttpKernel\\Debug\\StopwatchEvent')
            ->setMethods(array('getNow'))
            ->setConstructorArgs(array(0))
            ->getMock()
        ;

        $event
            ->expects($this->exactly(4))
            ->method('getNow')
            ->will($this->onConsecutiveCalls(10, 20, 30, 40))
        ;

        $this->assertEquals(
            array(array(0, 10), array(20, 40)),
            $event->start()->stop()->start()->setOrigin(10)->stop()->getPeriods()
        );
    }

    public function testMerge()
    {
        $e1 = $this
            ->getMockBuilder('Symfony\\Component\\HttpKernel\\Debug\\StopwatchEvent')
            ->setMethods(array('getNow'))
            ->setConstructorArgs(array(0))
            ->getMock()
        ;

        $e1
            ->expects($this->exactly(2))
            ->method('getNow')
            ->will($this->onConsecutiveCalls(0, 10))
        ;

        $e2 = $this
            ->getMockBuilder('Symfony\\Component\\HttpKernel\\Debug\\StopwatchEvent')
            ->setMethods(array('getNow'))
            ->setConstructorArgs(array(10))
            ->getMock()
        ;

        $e2
            ->expects($this->exactly(2))
            ->method('getNow')
            ->will($this->onConsecutiveCalls(50, 60))
        ;

        $this->assertEquals(
            array(array(0, 10), array(60, 70)),
            $e1->start()->stop()->merge($e2->start()->stop())->getPeriods()
        );
    }
}
