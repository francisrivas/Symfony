<?php

/*
 * This file is part of the Symfony package.
 * 
 * (c) Fabien Potencier <fabien@symfony.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\Core\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Util\PropertyPath;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\EventListener\TrimListener;
use Symfony\Component\Form\Extension\Core\Validator\DefaultValidator;
use Symfony\Component\EventDispatcher\EventDispatcher;

class FieldType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        if (null === $options['property_path']) {
            $options['property_path'] = $builder->getName();
        }

        if (false === $options['property_path'] || '' === $options['property_path']) {
            $options['property_path'] = null;
        } else {
            $options['property_path'] = new PropertyPath($options['property_path']);
        }

        $builder
            ->setRequired($options['required'])
            ->setReadOnly($options['read_only'])
            ->setErrorBubbling($options['error_bubbling'])
            ->setEmptyData($options['empty_data'])
            ->setAttribute('by_reference', $options['by_reference'])
            ->setAttribute('property_path', $options['property_path'])
            ->setAttribute('error_mapping', $options['error_mapping'])
            ->setAttribute('max_length', $options['max_length'])
            ->setAttribute('pattern', $options['pattern'])
            ->setAttribute('label', $options['label'] ?: $this->humanize($builder->getName()))
            ->setData($options['data'])
            ->addValidator(new DefaultValidator())
        ;

        if ($options['trim']) {
            $builder->addEventSubscriber(new TrimListener());
        }
    }

    public function buildView(FormView $view, FormInterface $form)
    {
        $name = $form->getName();

        if ($view->hasParent()) {
            $parentId = $view->getParent()->get('id');
            $parentFullName = $view->getParent()->get('full_name');
            $id = sprintf('%s_%s', $parentId, $name);
            $fullName = sprintf('%s[%s]', $parentFullName, $name);
        } else {
            $id = $name;
            $fullName = $name;
        }

        $types = array();
        foreach (array_reverse((array) $form->getTypes()) as $type) {
            $types[] = $type->getName();
        }

        $view
            ->set('form', $view)
            ->set('id', $id)
            ->set('name', $name)
            ->set('full_name', $fullName)
            ->set('errors', $form->getErrors())
            ->set('value', $form->getClientData())
            ->set('read_only', $form->isReadOnly())
            ->set('required', $form->isRequired())
            ->set('max_length', $form->getAttribute('max_length'))
            ->set('pattern', $form->getAttribute('pattern'))
            ->set('size', null)
            ->set('label', $form->getAttribute('label'))
            ->set('multipart', false)
            ->set('attr', array())
            ->set('types', $types)
        ;
    }

    public function getDefaultOptions(array $options)
    {
        $defaultOptions = array(
            'data'              => null,
            'data_class'        => null,
            'trim'              => true,
            'required'          => true,
            'read_only'         => false,
            'max_length'        => null,
            'pattern'           => null,
            'property_path'     => null,
            'by_reference'      => true,
            'error_bubbling'    => false,
            'error_mapping'     => array(),
            'label'             => null,
        );

        $class = isset($options['data_class']) ? $options['data_class'] : null;

        // If no data class is set explicitly and an object is passed as data,
        // use the class of that object as data class
        if (!$class && isset($options['data']) && is_object($options['data'])) {
            $defaultOptions['data_class'] = $class = get_class($options['data']);
        }

        if ($class) {
            $defaultOptions['empty_data'] = function () use ($class) {
                return new $class();
            };
        } else {
            $defaultOptions['empty_data'] = '';
        }

        return $defaultOptions;
    }

    public function createBuilder($name, FormFactoryInterface $factory, array $options)
    {
        return new FormBuilder($name, $factory, new EventDispatcher(), $options['data_class']);
    }

    public function getParent(array $options)
    {
        return null;
    }

    public function getName()
    {
        return 'field';
    }

    private function humanize($text)
    {
        return ucfirst(strtolower(str_replace('_', ' ', $text)));
    }
}
