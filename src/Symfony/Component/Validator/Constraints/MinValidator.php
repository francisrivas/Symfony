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
use Symfony\Component\Validator\ConstraintValidator;

class MinValidator extends ConstraintValidator
{
    public function isValid($value, Constraint $constraint)
    {
        if (null === $value) {
            return true;
        }

        if (!is_numeric($value)) {
            $this->setMessage($constraint->invalidMessage, array(
                '{{ value }}' => $value,
                '{{ limit }}' => $constraint->limit,
            ));

            return false;
        }

        if ($value < $constraint->limit) {
            $this->setMessage($constraint->message, array(
                '{{ value }}' => $value,
                '{{ limit }}' => $constraint->limit,
            ));

            return false;
        }

        return true;
    }
}
