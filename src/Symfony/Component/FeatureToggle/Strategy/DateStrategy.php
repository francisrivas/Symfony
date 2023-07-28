<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\FeatureToggle\Strategy;

use Symfony\Component\FeatureToggle\StrategyResult;
use InvalidArgumentException;
use Psr\Clock\ClockInterface;

final class DateStrategy implements StrategyInterface
{
    public function __construct(
        private readonly ClockInterface $clock,
        private readonly \DateTimeImmutable|null $from = null,
        private readonly \DateTimeImmutable|null $until = null,
        private readonly bool $includeFrom = true,
        private readonly bool $includeUntil = true,
    ) {
        if (null === $this->from && null === $this->until) {
            throw new InvalidArgumentException('Either from or until must be provided.');
        }
    }

    public function compute(): StrategyResult
    {
        $now = $this->clock->now();

        if (null !== $this->from) {
            if ($this->includeFrom && $this->from > $now) {
                return StrategyResult::Deny;
            }

            if (!$this->includeFrom && $this->from >= $now) {
                return StrategyResult::Deny;
            }
        }

        if (null !== $this->until) {
            if ($this->includeUntil && $this->until < $now) {
                return StrategyResult::Deny;
            }

            if (!$this->includeUntil && $this->until <= $now) {
                return StrategyResult::Deny;
            }
        }

        return StrategyResult::Grant;
    }
}
