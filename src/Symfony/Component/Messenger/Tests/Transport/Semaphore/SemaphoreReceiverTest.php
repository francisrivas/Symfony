<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Messenger\Tests\Transport\Semaphore;

use Symfony\Component\Serializer as SerializerComponent;
use Symfony\Component\Messenger\Tests\Fixtures\DummyMessage;
use Symfony\Component\Messenger\Transport\Semaphore\Connection;
use Symfony\Component\Messenger\Transport\Semaphore\SemaphoreEnvelope;
use Symfony\Component\Messenger\Transport\Semaphore\SemaphoreReceiver;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use PHPUnit\Framework\TestCase;

class SemaphoreReceiverTest extends TestCase
{
	public function testItReturnsTheDecodedMessageToTheHandler()
	{
		$serializer = new Serializer(
				new SerializerComponent\Serializer([new ObjectNormalizer()], ['json' => new JsonEncoder()])
		);
		
		$semaphoreEnvelope = $this->createSemaphoreEnvelope();
		$connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
		$connection->method('get')->willReturn($semaphoreEnvelope);
		
		$receiver = new SemaphoreReceiver($connection, $serializer);
		$actualEnvelopes = iterator_to_array($receiver->get());
		
		$this->assertCount(1, $actualEnvelopes);
		$this->assertEquals(new DummyMessage('Hi'), $actualEnvelopes[0]->getMessage());
	}
	
	private function createSemaphoreEnvelope(): SemaphoreEnvelope
	{
		$envelope = $this->getMockBuilder(SemaphoreEnvelope::class)->disableOriginalConstructor()->getMock();
		$envelope->method('getBody')->willReturn('{"message": "Hi"}');
		$envelope->method('getHeaders')->willReturn([
				'type' => DummyMessage::class,
		]);
		
		return $envelope;
	}
}
