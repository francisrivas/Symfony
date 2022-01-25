<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mailer\Bridge\Amazon\Transport;

use AsyncAws\Ses\Input\SendEmailRequest;
use AsyncAws\Ses\ValueObject\Destination;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;

/**
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class SesApiAsyncAwsTransport extends SesHttpAsyncAwsTransport
{
    public function __toString(): string
    {
        $configuration = $this->sesClient->getConfiguration();
        if (!$configuration->isDefault('endpoint')) {
            $endpoint = parse_url($configuration->get('endpoint'));
            $host = $endpoint['host'].($endpoint['port'] ?? null ? ':'.$endpoint['port'] : '');
        } else {
            $host = $configuration->get('region');
        }

        return sprintf('ses+api://%s@%s', $configuration->get('accessKeyId'), $host);
    }

    protected function getRequest(SentMessage $message): SendEmailRequest
    {
        $request = [
            'Destination' => new Destination([
                'ToAddresses' => $this->stringifyAddresses($message->getEnvelope()->getRecipients()),
            ]),
            'Content' => [
                'Raw' => [
                    'Data' => $message->toString(),
                ],
            ],
        ];

        if (($message->getOriginalMessage() instanceof Message)
            && $configurationSetHeader = $message->getOriginalMessage()->getHeaders()->get('X-SES-CONFIGURATION-SET')) {
            $request['ConfigurationSetName'] = $configurationSetHeader->getBodyAsString();
        }
        if (($message->getOriginalMessage() instanceof Message)
            && $sourceArnHeader = $message->getOriginalMessage()->getHeaders()->get('X-SES-SOURCE-ARN')) {
            $request['FromEmailAddressIdentityArn'] = $sourceArnHeader->getBodyAsString();
        }

        return new SendEmailRequest($request);
    }

    private function getRecipients(Email $email, Envelope $envelope): array
    {
        $emailRecipients = array_merge($email->getCc(), $email->getBcc());

        return array_filter($envelope->getRecipients(), function (Address $address) use ($emailRecipients) {
            return !\in_array($address, $emailRecipients, true);
        });
    }

    protected function stringifyAddresses(array $addresses): array
    {
        return array_map(function (Address $a) {
            // AWS does not support UTF-8 address
            if (preg_match('~[\x00-\x08\x10-\x19\x7F-\xFF\r\n]~', $name = $a->getName())) {
                return sprintf('=?UTF-8?B?%s?= <%s>',
                    base64_encode($name),
                    $a->getEncodedAddress()
                );
            }

            return $a->toString();
        }, $addresses);
    }
}
