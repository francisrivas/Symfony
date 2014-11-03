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
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * Used for the comparison of values.
 *
 * @author Daniel Holmes <daniel@danielholmes.org>
 *
 * @deprecated Deprecated as of Symfony 2.6, to be removed in version 3.0.
 *             Use {@link Expression} instead.
 */
abstract class AbstractComparison extends Constraint
{
    public $message;
    public $value;

    /**
     * {@inheritdoc}
     */
    public function __construct($options = null)
    {
        if (is_array($options) && !isset($options['value'])) {
            throw new ConstraintDefinitionException(sprintf(
                'The %s constraint requires the "value" option to be set.',
                get_class($this)
            ));
        }

        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption()
    {
        return 'value';
    }
}
