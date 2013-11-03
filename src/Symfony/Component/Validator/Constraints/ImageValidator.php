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
 * Validates whether a value is a valid image file and is valid
 * against minWidth, maxWidth, minHeight and maxHeight constraints
 *
 * @author Benjamin Dulau <benjamin.dulau@gmail.com>
 */
class ImageValidator extends FileValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $violations = count($this->context->getViolations());

        parent::validate($value, $constraint);

        $failed = count($this->context->getViolations()) !== $violations;

        if ($failed || null === $value || '' === $value) {
            return;
        }

        if (null === $constraint->minWidth && null === $constraint->maxWidth
            && null === $constraint->minHeight && null === $constraint->maxHeight
            && null === $constraint->minRatio && null === $constraint->maxRatio
            && $constraint->allowSquare && $constraint->allowLandscape && $constraint->allowPortrait) {
            return;
        }

        $size = @getimagesize($value);
        if (empty($size) || ($size[0] === 0) || ($size[1] === 0)) {
            $this->context->addViolation($constraint->sizeNotDetectedMessage, array(), $value, null, $constraint::ERROR_SIZE_NOT_DETECTED);

            return;
        }

        $width  = $size[0];
        $height = $size[1];

        if ($constraint->minWidth) {
            if (!ctype_digit((string) $constraint->minWidth)) {
                throw new ConstraintDefinitionException(sprintf('"%s" is not a valid minimum width', $constraint->minWidth));
            }

            if ($width < $constraint->minWidth) {
                $this->context->addViolation($constraint->minWidthMessage, array(
                    '{{ width }}'    => $width,
                    '{{ min_width }}' => $constraint->minWidth
                ), $value, null, $constraint::ERROR_MIN_WIDTH);

                return;
            }
        }

        if ($constraint->maxWidth) {
            if (!ctype_digit((string) $constraint->maxWidth)) {
                throw new ConstraintDefinitionException(sprintf('"%s" is not a valid maximum width', $constraint->maxWidth));
            }

            if ($width > $constraint->maxWidth) {
                $this->context->addViolation($constraint->maxWidthMessage, array(
                    '{{ width }}'    => $width,
                    '{{ max_width }}' => $constraint->maxWidth
                ), $value, null, $constraint::ERROR_MAX_WIDTH);

                return;
            }
        }

        if ($constraint->minHeight) {
            if (!ctype_digit((string) $constraint->minHeight)) {
                throw new ConstraintDefinitionException(sprintf('"%s" is not a valid minimum height', $constraint->minHeight));
            }

            if ($height < $constraint->minHeight) {
                $this->context->addViolation($constraint->minHeightMessage, array(
                    '{{ height }}'    => $height,
                    '{{ min_height }}' => $constraint->minHeight
                ), $value, null, $constraint::ERROR_MIN_HEIGHT);

                return;
            }
        }

        if ($constraint->maxHeight) {
            if (!ctype_digit((string) $constraint->maxHeight)) {
                throw new ConstraintDefinitionException(sprintf('"%s" is not a valid maximum height', $constraint->maxHeight));
            }

            if ($height > $constraint->maxHeight) {
                $this->context->addViolation($constraint->maxHeightMessage, array(
                    '{{ height }}'    => $height,
                    '{{ max_height }}' => $constraint->maxHeight
                ), $value, null, $constraint::ERROR_MAX_HEIGHT);
            }
        }

        $ratio = $width / $height;

        if (null !== $constraint->minRatio) {
            if (!is_numeric((string) $constraint->minRatio)) {
                throw new ConstraintDefinitionException(sprintf('"%s" is not a valid minimum ratio', $constraint->minRatio));
            }

            if ($ratio < $constraint->minRatio) {
                $this->context->addViolation($constraint->minRatioMessage, array(
                    '{{ ratio }}' => $ratio,
                    '{{ min_ratio }}' => $constraint->minRatio
                ), $value, null, $constraint::ERROR_MIN_RATIO);
            }
        }

        if (null !== $constraint->maxRatio) {
            if (!is_numeric((string) $constraint->maxRatio)) {
                throw new ConstraintDefinitionException(sprintf('"%s" is not a valid maximum ratio', $constraint->maxRatio));
            }

            if ($ratio > $constraint->maxRatio) {
                $this->context->addViolation($constraint->maxRatioMessage, array(
                    '{{ ratio }}' => $ratio,
                    '{{ max_ratio }}' => $constraint->maxRatio
                ), $value, null, $constraint::ERROR_MAX_RATIO);
            }
        }

        if (!$constraint->allowSquare && $width == $height) {
            $this->context->addViolation($constraint->allowSquareMessage, array(
                '{{ width }}' => $width,
                '{{ height }}' => $height
            ), $value, null, $constraint::ERROR_DONT_ALLOW_SQUARE);
        }

        if (!$constraint->allowLandscape && $width > $height) {
            $this->context->addViolation($constraint->allowLandscapeMessage, array(
                '{{ width }}' => $width,
                '{{ height }}' => $height
            ), $value, null, $constraint::ERROR_DONT_ALLOW_LANDSCAPE);
        }

        if (!$constraint->allowPortrait && $width < $height) {
            $this->context->addViolation($constraint->allowPortraitMessage, array(
                '{{ width }}' => $width,
                '{{ height }}' => $height
            ), $value, null, $constraint::ERROR_DONT_ALLOW_PORTRAIT);
        }

    }
}
