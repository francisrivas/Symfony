<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Webhook\Server;

use Symfony\Component\Serializer\SerializerInterface;

final class SerializerPayloadEncoder implements PayloadEncoderInterface
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {
    }

    public function encode(array $payload): string
    {
        return $this->serializer->serialize($payload, 'json');
    }
}
