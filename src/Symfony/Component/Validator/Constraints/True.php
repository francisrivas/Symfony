<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
class True extends Constraint
{
    const ERROR = '36f92ba4-67e3-4e0b-a5ca-fe819568ed0d';

    public $message = 'This value should be true.';
}
