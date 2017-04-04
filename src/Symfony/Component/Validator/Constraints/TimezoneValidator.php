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
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates whether a value is a valid timezone identifier.
 *
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class TimezoneValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Timezone) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Timezone');
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $value = (string) $value;
        $timezoneIds = \DateTimeZone::listIdentifiers($constraint->value);

        if ($timezoneIds && !in_array($value, $timezoneIds, true)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ timezone_group }}', $this->formatValue($value))
                ->setCode(Timezone::NO_SUCH_TIMEZONE_ERROR)
                ->addViolation();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption()
    {
        return 'value';
    }
}
