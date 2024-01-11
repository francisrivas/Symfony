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
 * Use this constraint to sequentially validate nested constraints.
 * Validation for the nested constraints collection will stop at first violation.
 *
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Sequentially extends Composite
{
    public array|Constraint $constraints = [];

    /**
     * @param array<string,mixed>|Constraint[]|null $constraints An array of validation constraints
     * @param string[]|null                         $groups
     */
    public function __construct(mixed $constraints = null, array $groups = null, mixed $payload = null)
    {
        parent::__construct($constraints ?? [], $groups, $payload);
    }

    public function getDefaultOption(): ?string
    {
        return 'constraints';
    }

    public function getRequiredOptions(): array
    {
        return ['constraints'];
    }

    protected function getCompositeOption(): string
    {
        return 'constraints';
    }

    public function getTargets(): string|array
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
