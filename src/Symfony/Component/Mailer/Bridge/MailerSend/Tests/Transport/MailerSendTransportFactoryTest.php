<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mailer\Bridge\MailerSend\Tests\Transport;

use Symfony\Component\Mailer\Bridge\MailerSend\Transport\MailerSendApiTransport;
use Symfony\Component\Mailer\Bridge\MailerSend\Transport\MailerSendSmtpTransport;
use Symfony\Component\Mailer\Bridge\MailerSend\Transport\MailerSendTransportFactory;
use Symfony\Component\Mailer\Test\TransportFactoryTestCase;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;

class MailerSendTransportFactoryTest extends TransportFactoryTestCase
{
    public function getFactory(): TransportFactoryInterface
    {
        return new MailerSendTransportFactory($this->getDispatcher(), $this->getClient(), $this->getLogger());
    }

    public function supportsProvider(): iterable
    {
        yield [
            new Dsn('mailersend', 'default'),
            true,
        ];

        yield [
            new Dsn('mailersend+smtp', 'default'),
            true,
        ];

        yield [
            new Dsn('mailersend+smtp', 'example.com'),
            true,
        ];

        yield [
            new Dsn('mailersend+api', 'default'),
            true,
        ];
    }

    public function createProvider(): iterable
    {
        yield [
            new Dsn('mailersend', 'default', self::USER, self::PASSWORD),
            new MailerSendSmtpTransport(self::USER, self::PASSWORD, $this->getDispatcher(), $this->getLogger()),
        ];

        yield [
            new Dsn('mailersend+smtp', 'default', self::USER, self::PASSWORD),
            new MailerSendSmtpTransport(self::USER, self::PASSWORD, $this->getDispatcher(), $this->getLogger()),
        ];

        yield [
            new Dsn('mailersend+smtp', 'default', self::USER, self::PASSWORD, 465),
            new MailerSendSmtpTransport(self::USER, self::PASSWORD, $this->getDispatcher(), $this->getLogger()),
        ];

        yield [
            new Dsn('mailersend+api', 'default', self::USER),
            new MailerSendApiTransport(self::USER, $this->getClient(), $this->getDispatcher(), $this->getLogger()),
        ];
    }

    public function unsupportedSchemeProvider(): iterable
    {
        yield [
            new Dsn('mailersend+foo', 'default', self::USER, self::PASSWORD),
            'The "mailersend+foo" scheme is not supported; supported schemes for mailer "mailersend" are: "mailersend", "mailersend+smtp", "mailersend+api".',
        ];
    }

    public function incompleteDsnProvider(): iterable
    {
        yield [new Dsn('mailersend+smtp', 'default', self::USER)];

        yield [new Dsn('mailersend+smtp', 'default', null, self::PASSWORD)];

        yield [new Dsn('mailersend+api', 'default')];
    }
}
