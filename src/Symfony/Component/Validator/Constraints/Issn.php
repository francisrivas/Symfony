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
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class Issn extends Constraint
{
    public $issnInvalidFormatMessage = 'This value has not valid ISSN format.';
    public $issnInvalidValueMessage = 'This value is not a valid ISSN.';
    public $disallowLowerCasedX;
    public $disallowNonHyphenated;
}
