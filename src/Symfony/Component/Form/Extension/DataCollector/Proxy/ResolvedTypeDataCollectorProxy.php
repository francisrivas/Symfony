<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\DataCollector\Proxy;

use Symfony\Component\Form\Extension\DataCollector\FormDataCollectorInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ResolvedFormTypeInterface;

/**
 * Proxy that invokes a data collector when creating a form and its view.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ResolvedTypeDataCollectorProxy implements ResolvedFormTypeInterface
{
    private $proxiedType;
    private $dataCollector;

    public function __construct(ResolvedFormTypeInterface $proxiedType, FormDataCollectorInterface $dataCollector)
    {
        $this->proxiedType = $proxiedType;
        $this->dataCollector = $dataCollector;
    }

    public function getBlockPrefix()
    {
        return $this->proxiedType->getBlockPrefix();
    }

    public function getParent()
    {
        return $this->proxiedType->getParent();
    }

    public function getInnerType()
    {
        return $this->proxiedType->getInnerType();
    }

    public function getTypeExtensions()
    {
        return $this->proxiedType->getTypeExtensions();
    }

    public function createBuilder(FormFactoryInterface $factory, string $name, array $options = [])
    {
        $builder = $this->proxiedType->createBuilder($factory, $name, $options);

        $builder->setAttribute('data_collector/passed_options', $options);
        $builder->setType($this);

        return $builder;
    }

    public function createView(FormInterface $form, FormView $parent = null)
    {
        return $this->proxiedType->createView($form, $parent);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->proxiedType->buildForm($builder, $options);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $this->proxiedType->buildView($view, $form, $options);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->proxiedType->finishView($view, $form, $options);

        // Remember which view belongs to which form instance, so that we can
        // get the collected data for a view when its form instance is not
        // available (e.g. CSRF token)
        $this->dataCollector->associateFormWithView($form, $view);

        // Since the CSRF token is only present in the FormView tree, we also
        // need to check the FormView tree instead of calling isRoot() on the
        // FormInterface tree
        if (null === $view->parent) {
            $this->dataCollector->collectViewVariables($view);

            // Re-assemble data, in case FormView instances were added, for
            // which no FormInterface instances were present (e.g. CSRF token).
            // Since finishView() is called after finishing the views of all
            // children, we can safely assume that information has been
            // collected about the complete form tree.
            $this->dataCollector->buildFinalFormTree($form, $view);
        }
    }

    public function getOptionsResolver()
    {
        return $this->proxiedType->getOptionsResolver();
    }
}
