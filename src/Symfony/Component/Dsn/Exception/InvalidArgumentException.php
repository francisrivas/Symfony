<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Symfony\Component\Dsn\Exception;

/**
 * Base InvalidArgumentException for the Dsn component.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
}
