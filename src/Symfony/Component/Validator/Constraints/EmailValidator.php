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
use Egulias\EmailValidator\EmailValidator as StrictEmailValidator;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
class EmailValidator extends ConstraintValidator
{

    /**
     * isStrict
     *
     * @var Boolean
     */
    protected $isStrict;

    public function __construct($strict)
    {
        $this->isStrict = $strict;
    }

    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $value = (string) $value;
        if ($constraint->strict === null) {
            $constraint->strict = $this->isStrict;
        }

        if ($constraint->strict === true && class_exists('\Egulias\EmailValidator\EmailValidator')) {
            $strictValidator = new StrictEmailValidator();
            $valid = $strictValidator->isValid($value, $constraint->checkMX);
        } elseif ($constraint->strict === true) {
            throw new \RuntimeException('Strict email validation requires egulias/email-validator');
        } else {
            $valid = preg_match('/.+\@.+\..+/', $value);
        }

        if ($valid && $constraint->checkHost) {
            $host = substr($value, strpos($value, '@') + 1);
            // Check for host DNS resource records
            $valid = $this->checkHost($host);
        }

        if (!$valid) {
            $this->context->addViolation($constraint->message, array('{{ value }}' => $value));
        }
    }

    /**
     * Check if one of MX, A or AAAA DNS RR exists.
     *
     * @param string $host Host
     *
     * @return Boolean
     */
    private function checkHost($host)
    {
        return $this->checkMX($host) || (checkdnsrr($host, "A") || checkdnsrr($host, "AAAA"));
    }
}
