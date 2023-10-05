<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\FeatureFlags\Strategy;

use Symfony\Component\FeatureFlags\StrategyResult;

final class NotStrategy implements StrategyInterface, OuterStrategyInterface
{
    public function __construct(
        private readonly StrategyInterface $inner,
    ) {
    }

    public function compute(): StrategyResult
    {
        $innerResult = $this->inner->compute();

        return match ($innerResult) {
            StrategyResult::Abstain => StrategyResult::Abstain,
            StrategyResult::Grant   => StrategyResult::Deny,
            StrategyResult::Deny    => StrategyResult::Grant,
        };
    }

    public function getInnerStrategy(): StrategyInterface
    {
        return $this->inner;
    }
}
