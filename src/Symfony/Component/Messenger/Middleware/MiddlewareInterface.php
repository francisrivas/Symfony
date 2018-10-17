<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Messenger\Middleware;

/**
 * @author Samuel Roze <samuel.roze@gmail.com>
 */
interface MiddlewareInterface
{
    /**
     * @param object $message
     */
    public function handle($message, callable $next): void;
}
