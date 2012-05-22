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

interface FormTypeExtensionInterface
{
    /**
     * Builds the form.
     *
     * This method gets called after the extended type has built the form to
     * further modify it.
     *
     * @see FormTypeInterface::buildForm()
     *
     * @param FormBuilder $builder The form builder
     * @param array       $options The options
     */
    function buildForm(FormBuilder $builder, array $options);

    /**
     * Builds the view.
     *
     * This method gets called after the extended type has built the view to
     * further modify it.
     *
     * @see FormTypeInterface::buildView()
     *
     * @param FormView      $view The view
     * @param FormInterface $form The form
     */
    function buildView(FormView $view, FormInterface $form);

    /**
     * Builds the view.
     *
     * This method gets called after the extended type has built the view to
     * further modify it.
     *
     * @see FormTypeInterface::buildViewBottomUp()
     *
     * @param FormView      $view The view
     * @param FormInterface $form The form
     */
    function buildViewBottomUp(FormView $view, FormInterface $form);

    /**
     * Overrides the default options form the extended type.
     *
     * @return array
     */
    function getDefaultOptions();

    /**
     * Returns the allowed option values for each option (if any).
     *
     * @return array The allowed option values
     */
    function getAllowedOptionValues();


    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    function getExtendedType();
}
