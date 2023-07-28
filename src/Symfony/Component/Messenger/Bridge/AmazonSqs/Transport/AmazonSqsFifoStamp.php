<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Messenger\Bridge\AmazonSqs\Transport;

use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class AmazonSqsFifoStamp implements NonSendableStampInterface
{
    private ?string $messageGroupId;
    private ?string $messageDeduplicationId;

    public function __construct(string $messageGroupId = null, string $messageDeduplicationId = null)
    {
        $this->messageGroupId = $messageGroupId;
        $this->messageDeduplicationId = $messageDeduplicationId;
    }

    public function getMessageGroupId(): ?string
    {
        return $this->messageGroupId;
    }

    public function getMessageDeduplicationId(): ?string
    {
        return $this->messageDeduplicationId;
    }
}
