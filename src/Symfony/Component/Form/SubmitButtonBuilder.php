<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form;

/**
 * A builder for {@link SubmitButton} instances.
 *

 */
class SubmitButtonBuilder extends ButtonBuilder
{
    /**
     * Creates the button.
     *
     * @return SubmitButton The button
     */
    public function getForm()
    {
        return new SubmitButton($this->getFormConfig());
    }
}
