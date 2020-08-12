<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Scheduler;

use Symfony\Component\Scheduler\EventListener\SchedulerSubscriberInterface;
use Symfony\Component\Scheduler\Exception\InvalidArgumentException;
use function array_key_exists;
use function array_filter;
use function count;
use function in_array;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SchedulerRegistry implements SchedulerRegistryInterface
{
    /**
     * @var SchedulerInterface[]
     */
    private $schedulers = [];
    private $subscribers;

    /**
     * @param iterable|SchedulerSubscriberInterface[] $subscribers
     */
    public function __construct(iterable $subscribers)
    {
        $this->subscribers = $subscribers;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name): SchedulerInterface
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" does not exist.', $name));
        }

        return $this->schedulers[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->schedulers);
    }

    /**
     * {@inheritdoc}
     */
    public function register(string $name, SchedulerInterface $scheduler): void
    {
        if ($this->has($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" scheduler is already registered, consider using %s::override() if it need to be override', $name, self::class));
        }

        foreach ($this->subscribers as $subscriber) {
            if (in_array($name, $subscriber::getSubscribedWorkers()) || in_array('*', $subscriber::getSubscribedWorkers())) {
                $scheduler->addSubscriber($subscriber);
            }
        }

        $this->schedulers[$name] = $scheduler;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(\Closure $filter): array
    {
        return array_filter($this->schedulers, $filter, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $name): void
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" scheduler does not exist.', $name));
        }

        unset($this->schedulers[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function override(string $name, SchedulerInterface $scheduler): void
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(sprintf('The "%s" does not exist.', $name));
        }

        $this->schedulers[$name] = $scheduler;
    }

    /**
     * @return array<string,SchedulerInterface>
     */
    public function toArray(): array
    {
        return $this->schedulers;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->schedulers);
    }
}
