<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mailer\Bridge\Sendinblue\Transport;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

trigger_deprecation('symfony/sendinblue-mailer', '6.4', 'The "%s" class is deprecated, use "%s" instead.', SendinblueSmtpTransport::class, 'Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoSmtpTransport');

/**
 * @author Yann LUCAS
 *
 * @deprecated since Symfony 6.4, use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoSmtpTransport instead
 */
final class SendinblueSmtpTransport extends EsmtpTransport
{
    public function __construct(string $username, #[\SensitiveParameter] string $password, EventDispatcherInterface $dispatcher = null, LoggerInterface $logger = null)
    {
        parent::__construct('smtp-relay.sendinblue.com', 465, true, $dispatcher, $logger);

        $this->setUsername($username);
        $this->setPassword($password);
    }
}
