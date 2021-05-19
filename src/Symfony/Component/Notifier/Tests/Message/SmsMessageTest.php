<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Tests\Message;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Notifier\Message\SmsMessage;

/**

 */
class SmsMessageTest extends TestCase
{
    public function testCanBeConstructed()
    {
        $message = new SmsMessage('+3312345678', 'subject');

        $this->assertSame('subject', $message->getSubject());
        $this->assertSame('+3312345678', $message->getPhone());
    }

    public function testEnsureNonEmptyPhoneOnConstruction()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"Symfony\Component\Notifier\Message\SmsMessage" needs a phone number, it cannot be empty.');

        new SmsMessage('', 'subject');
    }

    public function testSetPhone()
    {
        $message = new SmsMessage('+3312345678', 'subject');

        $this->assertSame('+3312345678', $message->getPhone());

        $message->phone('+4912345678');

        $this->assertSame('+4912345678', $message->getPhone());
    }

    public function testEnsureNonEmptyPhoneOnSet()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"Symfony\Component\Notifier\Message\SmsMessage" needs a phone number, it cannot be empty.');

        $message = new SmsMessage('+3312345678', 'subject');

        $this->assertSame('+3312345678', $message->getPhone());

        $message->phone('');
    }
}
