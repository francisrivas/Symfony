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
class Url extends Constraint
{
    const ERROR = '23af3103-a5ca-4459-b0ae-3a2043479a98';

    public $message = 'This value is not a valid URL.';
    public $protocols = array('http', 'https');
}
