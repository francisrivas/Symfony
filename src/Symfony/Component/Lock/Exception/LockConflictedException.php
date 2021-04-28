<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Lock\Exception;

/**
 * LockConflictedException is thrown when a lock is acquired by someone else.
 *
 * In non-blocking mode it is caught by acquire() and acquireRead().
 * @see Lock::acquire()
 * @see Lock::acquireRead()
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class LockConflictedException extends \RuntimeException implements ExceptionInterface
{
}
