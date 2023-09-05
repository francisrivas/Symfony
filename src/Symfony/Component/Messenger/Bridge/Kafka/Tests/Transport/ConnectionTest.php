<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Messenger\Bridge\Kafka\Tests\Transport;

use PHPUnit\Framework\TestCase;
use RdKafka\Exception;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use RdKafka\Producer;
use RdKafka\ProducerTopic;
use Symfony\Component\Messenger\Bridge\Kafka\Tests\Fixtures\FakeMessage;
use Symfony\Component\Messenger\Bridge\Kafka\Tests\Fixtures\TestKafkaFactory;
use Symfony\Component\Messenger\Bridge\Kafka\Transport\Connection;
use Symfony\Component\Messenger\Bridge\Kafka\Transport\KafkaFactory;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\Exception\TransportException;

/**
 * @requires extension rdkafka
 */
class ConnectionTest extends TestCase
{
    private KafkaConsumer $consumer;
    private Producer $producer;
    private KafkaFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new TestKafkaFactory(
            $this->consumer = $this->createMock(KafkaConsumer::class),
            $this->producer = $this->createMock(Producer::class),
        );
    }

    public function testFromDsnWithMinimumConfig()
    {
        self::assertInstanceOf(
            Connection::class,
            Connection::fromDsn(
                'kafka://localhost:9092',
                [
                    'consumer' => [
                        'topics' => ['consumer-topic'],
                        'conf_options' => [
                            'group.id' => 'groupId',
                        ],
                    ],
                    'producer' => [
                        'topic' => 'producer-topic',
                    ],
                ],
                $this->factory,
            ),
        );
    }

    public function testFromDsnWithInvalidOption()
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid option(s) "invalid" passed to the Kafka Messenger transport.');
        self::expectExceptionCode(0);
        Connection::fromDsn(
            'kafka://localhost:1000',
            [
                'invalid' => true,
            ],
            $this->factory,
        );
    }

    public function testFromDsnWithNoConsumerOrProducerOption()
    {
        self::expectException(LogicException::class);
        self::expectExceptionMessage('At least one of "consumer" or "producer" options is required for the Kafka Messenger transport.');
        self::expectExceptionCode(0);
        Connection::fromDsn(
            'kafka://localhost:1000',
            [],
            $this->factory,
        );
    }

    public function testFromDsnWithInvalidConsumerOption()
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid option(s) "invalid" passed to the Kafka Messenger transport consumer.');
        self::expectExceptionCode(0);
        Connection::fromDsn(
            'kafka://localhost:1000',
            [
                'consumer' => [
                    'invalid' => true,
                ],
            ],
            $this->factory,
        );
    }

    public function testFromDsnWithConsumeTopicsNotArray()
    {
        self::expectException(LogicException::class);
        self::expectExceptionMessage('The "topics" option type must be array, "string" given in the Kafka Messenger transport consumer.');
        self::expectExceptionCode(0);
        Connection::fromDsn(
            'kafka://localhost:9092',
            [
                'consumer' => [
                    'topics' => 'this-is-a-string',
                    'conf_options' => [
                        'group.id' => 'php-unit-group-id',
                    ],
                ],
            ],
            $this->factory,
        );
    }

    public function testFromDsnWithConsumeTimeoutNonInteger()
    {
        self::expectException(LogicException::class);
        self::expectExceptionMessage('The "consume_timeout_ms" option type must be integer, "string" given in the Kafka Messenger transport consumer.');
        self::expectExceptionCode(0);
        Connection::fromDsn(
            'kafka://localhost:9092',
            [
                'consumer' => [
                    'topics' => ['php-unit-consumer'],
                    'consume_timeout_ms' => 'flush',
                    'conf_options' => [
                        'group.id' => 'php-unit-group-id',
                    ],
                ],
            ],
            $this->factory,
        );
    }

    public function testFromDsnWithInvalidConsumerKafkaConfOption()
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid conf_options option "invalid" passed to the Kafka Messenger transport.');
        self::expectExceptionCode(0);
        Connection::fromDsn(
            'kafka://localhost:1000',
            [
                'consumer' => [
                    'conf_options' => [
                        'invalid' => true,
                    ],
                ],
            ],
            $this->factory,
        );
    }

    public function testFromDsnWithKafkaConfGroupIdMissing()
    {
        self::expectException(LogicException::class);
        self::expectExceptionMessage('The conf_option(s) "group.id", "metadata.broker.list" are required for the Kafka Messenger transport consumer.');
        self::expectExceptionCode(0);

        $connection = Connection::fromDsn(
            'kafka://localhost:9092',
            [
                'consumer' => [
                    'topics' => ['php-unit-consumer'],
                    'conf_options' => [
                    ],
                ],
            ],
            $this->factory,
        );
    }

    public function testFromDsnWithInvalidConsumerKafkaConfOptionNotAString()
    {
        self::expectException(LogicException::class);
        self::expectExceptionMessage('Kafka config value "client.id" must be a string, got "bool".');
        self::expectExceptionCode(0);
        Connection::fromDsn(
            'kafka://localhost:1000',
            [
                'consumer' => [
                    'topics' => ['php-unit-consumer'],
                    'conf_options' => [
                        'client.id' => true,
                    ],
                ],
            ],
            $this->factory,
        );
    }

    public function testFromDsnWithInvalidProducerOption()
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid option(s) "invalid" passed to the Kafka Messenger transport producer.');
        self::expectExceptionCode(0);
        Connection::fromDsn(
            'kafka://localhost:1000',
            [
                'producer' => [
                    'invalid' => true,
                ],
            ],
            $this->factory,
        );
    }

    public function testFromDsnWithInvalidProducerKafkaConfOption()
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid conf_options option "invalid" passed to the Kafka Messenger transport.');
        self::expectExceptionCode(0);
        Connection::fromDsn(
            'kafka://localhost:1000',
            [
                'producer' => [
                    'conf_options' => [
                        'invalid' => true,
                    ],
                ],
            ],
            $this->factory,
        );
    }

    public function testFromDsnWithInvalidProducerKafkaConfOptionNotAString()
    {
        self::expectException(LogicException::class);
        self::expectExceptionMessage('Kafka config value "client.id" must be a string, got "bool".');
        self::expectExceptionCode(0);
        Connection::fromDsn(
            'kafka://localhost:1000',
            [
                'producer' => [
                    'conf_options' => [
                        'client.id' => true,
                    ],
                ],
            ],
            $this->factory,
        );
    }

    public function testFromDsnWithProducerPollTimeoutNonInteger()
    {
        self::expectException(LogicException::class);
        self::expectExceptionMessage('The "poll_timeout_ms" option type must be integer, "string" given in the Kafka Messenger transport producer.');
        self::expectExceptionCode(0);

        $connection = Connection::fromDsn(
            'kafka://localhost:9092',
            [
                'producer' => [
                    'topic' => 'php-unit-producer-topic',
                    'poll_timeout_ms' => 'poll',
                ],
            ],
            $this->factory,
        );
    }

    public function testFromDsnWithFlushTimeoutNonInteger()
    {
        self::expectException(LogicException::class);
        self::expectExceptionMessage('The "flush_timeout_ms" option type must be integer, "string" given in the Kafka Messenger transport producer.');
        self::expectExceptionCode(0);
        $connection = Connection::fromDsn(
            'kafka://localhost:9092',
            [
                'producer' => [
                    'topic' => 'php-unit-producer-topic',
                    'flush_timeout_ms' => 'flush',
                ],
            ],
            $this->factory,
        );
    }

    public function testPublish()
    {
        $connection = Connection::fromDsn(
            'kafka://localhost:9092',
            [
                'producer' => [
                    'topic' => 'php-unit-producer-topic',
                ],
            ],
            $this->factory,
        );

        $this->producer->expects($this->once())
            ->method('newTopic')
            ->with('php-unit-producer-topic')
            ->willReturn($topic = $this->createMock(ProducerTopic::class))
        ;

        $topic->expects($this->once())
            ->method('producev')
            ->with(\RD_KAFKA_PARTITION_UA, \RD_KAFKA_MSG_F_BLOCK, 'body');

        $this->producer->expects($this->once())->method('poll')->with(0);
        $this->producer->expects($this->once())->method('flush')->with(10000);

        $connection->publish(\RD_KAFKA_PARTITION_UA, \RD_KAFKA_MSG_F_BLOCK, 'body', null, ['type' => FakeMessage::class]);
    }

    public function testPublishWithTopicMissingException()
    {
        $connection = Connection::fromDsn(
            'kafka://localhost:9092',
            [
                'producer' => [],
            ],
            $this->factory,
        );

        $this->producer->expects($this->never())->method('newTopic');
        $this->producer->expects($this->never())->method('poll');
        $this->producer->expects($this->never())->method('flush');

        self::expectException(LogicException::class);
        self::expectExceptionMessage('No topic configured for the producer.');
        self::expectExceptionCode(0);

        $connection->publish(\RD_KAFKA_PARTITION_UA, \RD_KAFKA_MSG_F_BLOCK, 'body', null, ['type' => FakeMessage::class]);
    }

    public function testPublishWithCustomOptions()
    {
        $connection = Connection::fromDsn(
            'kafka://localhost:9092',
            [
                'producer' => [
                    'topic' => 'php-unit-producer-topic',
                    'poll_timeout_ms' => 10,
                    'flush_timeout_ms' => 20000,
                ],
            ],
            $this->factory,
        );

        $body = 'body';
        $headers = ['type' => FakeMessage::class];
        $partition = 1;
        $messageFlags = 0;
        $key = 'key';

        $this->producer->expects($this->once())
            ->method('newTopic')
            ->with('php-unit-producer-topic')
            ->willReturn($topic = $this->createMock(ProducerTopic::class))
        ;
        $topic->expects($this->once())
            ->method('producev')
            ->with($partition, $messageFlags, $body, $key, $headers);

        $this->producer->expects($this->once())->method('poll')->with(10);
        $this->producer->expects($this->once())->method('flush')->with(20000);

        $connection->publish(
            body: $body,
            headers: $headers,
            partition: $partition,
            messageFlags: $messageFlags,
            key: $key,
        );
    }

    public function testGet()
    {
        $connection = Connection::fromDsn(
            'kafka://localhost:9092',
            [
                'consumer' => [
                    'topics' => ['php-unit-consumer'],
                    'conf_options' => [
                        'group.id' => 'php-unit-group-id',
                    ],
                ],
            ],
            $this->factory,
        );

        $message = new Message();
        $message->partition = 0;
        $message->err = \RD_KAFKA_RESP_ERR_NO_ERROR;

        $this->consumer->expects($this->once())->method('subscribe')->with(['php-unit-consumer']);
        $this->consumer->expects($this->once())->method('consume')
            ->with(10000)->willReturn($message);

        $connection->get();
    }

    public function testGetWithConsumeException()
    {
        $connection = Connection::fromDsn(
            'kafka://localhost:9092',
            [
                'consumer' => [
                    'topics' => ['php-unit-consumer'],
                    'consume_timeout_ms' => 20000,
                    'conf_options' => [
                        'group.id' => 'php-unit-group-id',
                    ],
                ],
            ],
            $this->factory,
        );

        $this->consumer->expects($this->once())->method('subscribe')->with(['php-unit-consumer']);
        $this->consumer->expects($this->once())->method('consume')
            ->with(20000)->willThrowException(new Exception('kafka consume error', 1));

        self::expectException(TransportException::class);
        self::expectExceptionMessage('kafka consume error');
        self::expectExceptionCode(0);

        $connection->get();
    }

    public function testGetWithCustomOptions()
    {
        $connection = Connection::fromDsn(
            'kafka://localhost:9092',
            [
                'consumer' => [
                    'topics' => ['php-unit-consumer'],
                    'consume_timeout_ms' => 20000,
                    'conf_options' => [
                        'group.id' => 'php-unit-group-id',
                    ],
                ],
            ],
            $this->factory,
        );

        $message = new Message();
        $message->partition = 0;
        $message->err = \RD_KAFKA_RESP_ERR_NO_ERROR;
        $this->consumer->expects($this->once())->method('subscribe')->with(['php-unit-consumer']);
        $this->consumer->expects($this->once())->method('consume')
            ->with(20000)->willReturn($message);

        $connection->get();
    }
}
