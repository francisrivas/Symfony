<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Channel;

use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\Message\DesktopMessage;
use Symfony\Component\Notifier\Notification\DesktopNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

/**
 * @author Ahmed Ghanem <ahmedghanem7361@gmail.com>
 */
class DesktopChannel extends AbstractChannel
{
    /**
     * @throws TransportExceptionInterface
     * @throws ExceptionInterface
     */
    public function notify(
        Notification $notification,
        RecipientInterface $recipient,
        ?string $transportName = null,
    ): void {
        if ($notification instanceof DesktopNotificationInterface) {
            $message = $notification->asDesktopMessage($recipient, $transportName);
        }

        $message ??= DesktopMessage::fromNotification($notification);

        if (null !== $transportName) {
            $message->setTransport($transportName);
        }

        (null === $this->bus) ? $this->transport->send($message) : $this->bus->dispatch($message);
    }

    public function supports(Notification $notification, RecipientInterface $recipient): bool
    {
        return true;
    }
}
