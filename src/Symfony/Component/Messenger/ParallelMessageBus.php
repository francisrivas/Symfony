<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Messenger;

use Amp\Parallel\Worker\ContextWorkerPool;
use Symfony\Component\Messenger\Stamp\FutureStamp;

use function Amp\async;
use function Amp\Parallel\Worker\workerPool;

class ParallelMessageBus implements MessageBusInterface
{
    public static ?ContextWorkerPool $worker = null;

    public function __construct(private array $something, private readonly string $env, private readonly string $debug, private readonly string $projectdir)
    {
    }

    public function dispatch(object $message, array $stamps = []): Envelope
    {
        $worker = (self::$worker ??= workerPool());

        $envelope = Envelope::wrap($message, $stamps);
        $task = new DispatchTask($envelope, $stamps, $this->env, $this->debug, $this->projectdir);

        $future = async(function () use ($worker, $task) {
            return $worker->submit($task);
        });

        return $envelope->with(new FutureStamp($future));
    }
}
