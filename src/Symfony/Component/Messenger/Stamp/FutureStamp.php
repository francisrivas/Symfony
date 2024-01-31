<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Messenger\Stamp;

use Amp\Future;

readonly class FutureStamp implements StampInterface
{
    public function __construct(private Future $future)
    {
    }

    public function getFuture(): Future
    {
        return $this->future;
    }
}
