<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\Slack\Block;

/**

 */
abstract class AbstractSlackBlock implements SlackBlockInterface
{
    protected $options = [];

    public function toArray(): array
    {
        return $this->options;
    }
}
