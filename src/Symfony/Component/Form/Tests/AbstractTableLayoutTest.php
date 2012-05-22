<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Tests;

use Symfony\Component\Form\FormError;

abstract class AbstractTableLayoutTest extends AbstractLayoutTest
{
    public function testRow()
    {
        $form = $this->factory->createNamed('text', 'name');
        $form->addError(new FormError('Error!'));
        $view = $form->createView();
        $html = $this->renderRow($view);

        $this->assertMatchesXpath($html,
'/tr
    [
        ./td
            [./label[@for="name"]]
        /following-sibling::td
            [
                ./ul
                    [./li[.="[trans]Error![/trans]"]]
                    [count(./li)=1]
                    [@class="errors"]
                /following-sibling::input[@id="name"]
            ]
    ]
'
        );
    }

    public function testRepeatedRow()
    {
        $form = $this->factory->createNamed('repeated', 'name');
        $html = $this->renderRow($form->createView());

        $this->assertMatchesXpath($html,
'/tr
    [
        ./td
            [./label[@for="name_first"]]
        /following-sibling::td
            [./input[@id="name_first"]]
    ]
/following-sibling::tr
    [
        ./td
            [./label[@for="name_second"]]
        /following-sibling::td
            [./input[@id="name_second"]]
    ]
    [count(../tr)=3]
/following-sibling::tr[@style="display: none"]
    [./td[@colspan="2"]/input
        [@type="hidden"]
        [@id="name__token"]
    ]
'
        );
    }

    public function testRepeatedRowWithErrors()
    {
        $form = $this->factory->createNamed('repeated', 'name');
        $form->addError(new FormError('Error!'));
        $view = $form->createView();
        $html = $this->renderRow($view);

        $this->assertMatchesXpath($html,
'/tr
    [./td[@colspan="2"]/ul
        [@class="errors"]
        [./li[.="[trans]Error![/trans]"]]
    ]
/following-sibling::tr
    [
        ./td
            [./label[@for="name_first"]]
        /following-sibling::td
            [./input[@id="name_first"]]
    ]
/following-sibling::tr
    [
        ./td
            [./label[@for="name_second"]]
        /following-sibling::td
            [./input[@id="name_second"]]
    ]
    [count(../tr)=4]
/following-sibling::tr[@style="display: none"]
    [./td[@colspan="2"]/input
        [@type="hidden"]
        [@id="name__token"]
    ]
'
        );
    }

    public function testRest()
    {
        $view = $this->factory->createNamedBuilder('form', 'name')
            ->add('field1', 'text')
            ->add('field2', 'repeated')
            ->add('field3', 'text')
            ->add('field4', 'text')
            ->getForm()
            ->createView();

        // Render field2 row -> does not implicitely call renderWidget because
        // it is a repeated field!
        $this->renderRow($view['field2']);

        // Render field3 widget
        $this->renderWidget($view['field3']);

        // Rest should only contain field1 and field4
        $html = $this->renderRest($view);

        $this->assertMatchesXpath($html,
'/tr
    [
        ./td
            [./label[@for="name_field1"]]
        /following-sibling::td
            [./input[@id="name_field1"]]
    ]
/following-sibling::tr
    [
        ./td
            [./label[@for="name_field4"]]
        /following-sibling::td
            [./input[@id="name_field4"]]
    ]
    [count(../tr)=3]
    [count(..//label)=2]
    [count(..//input)=3]
/following-sibling::tr[@style="display: none"]
    [./td[@colspan="2"]/input
        [@type="hidden"]
        [@id="name__token"]
    ]
'
        );
    }

    public function testCollection()
    {
        $form = $this->factory->createNamed('collection', 'name', array('a', 'b'), array(
            'type' => 'text',
        ));

        $this->assertWidgetMatchesXpath($form->createView(), array(),
'/table
    [
        ./tr[./td/input[@type="text"][@value="a"]]
        /following-sibling::tr[./td/input[@type="text"][@value="b"]]
        /following-sibling::tr[@style="display: none"][./td[@colspan="2"]/input[@type="hidden"][@id="name__token"]]
    ]
    [count(./tr[./td/input])=3]
'
        );
    }

    public function testEmptyCollection()
    {
        $form = $this->factory->createNamed('collection', 'name', array(), array(
            'type' => 'text',
        ));

        $this->assertWidgetMatchesXpath($form->createView(), array(),
'/table
    [./tr[@style="display: none"][./td[@colspan="2"]/input[@type="hidden"][@id="name__token"]]]
    [count(./tr[./td/input])=1]
'
        );
    }

    public function testForm()
    {
        $view = $this->factory->createNamedBuilder('form', 'name')
            ->add('firstName', 'text')
            ->add('lastName', 'text')
            ->getForm()
            ->createView();

        $this->assertWidgetMatchesXpath($view, array(),
'/table
    [
        ./tr
            [
                ./td
                    [./label[@for="name_firstName"]]
                /following-sibling::td
                    [./input[@id="name_firstName"]]
            ]
        /following-sibling::tr
            [
                ./td
                    [./label[@for="name_lastName"]]
                /following-sibling::td
                    [./input[@id="name_lastName"]]
            ]
        /following-sibling::tr[@style="display: none"]
            [./td[@colspan="2"]/input
                [@type="hidden"]
                [@id="name__token"]
            ]
    ]
    [count(.//input)=3]
'
        );
    }

    // https://github.com/symfony/symfony/issues/2308
    public function testNestedFormError()
    {
        $form = $this->factory->createNamedBuilder('form', 'name')
            ->add($this->factory
                ->createNamedBuilder('form', 'child', null, array('error_bubbling' => false))
                ->add('grandChild', 'form')
            )
            ->getForm();

        $form->get('child')->addError(new FormError('Error!'));

        $this->assertWidgetMatchesXpath($form->createView(), array(),
'/table
    [
        ./tr/td/table
            [@id="name_child"]
            [./tr/td/ul[@class="errors"]/li[.="[trans]Error![/trans]"]]
    ]
    [count(.//li[.="[trans]Error![/trans]"])=1]
'
        );
    }

    public function testCsrf()
    {
        $this->csrfProvider->expects($this->any())
            ->method('generateCsrfToken')
            ->will($this->returnValue('foo&bar'));

        $form = $this->factory->createNamedBuilder('form', 'name')
            ->add($this->factory
                // No CSRF protection on nested forms
                ->createNamedBuilder('form', 'child')
                ->add($this->factory->createNamedBuilder('text', 'grandchild'))
            )
            ->getForm();

        $this->assertWidgetMatchesXpath($form->createView(), array(),
'/table
    [
        ./tr[@style="display: none"]
            [./td[@colspan="2"]/input
                [@type="hidden"]
                [@id="name__token"]
            ]
    ]
    [count(.//input[@type="hidden"])=1]
'
        );
    }

    public function testRepeated()
    {
        $form = $this->factory->createNamed('repeated', 'name', 'foobar', array(
            'type' => 'text',
        ));

        $this->assertWidgetMatchesXpath($form->createView(), array(),
'/table
    [
        ./tr
            [
                ./td
                    [./label[@for="name_first"]]
                /following-sibling::td
                    [./input[@type="text"][@id="name_first"]]
            ]
        /following-sibling::tr
            [
                ./td
                    [./label[@for="name_second"]]
                /following-sibling::td
                    [./input[@type="text"][@id="name_second"]]
            ]
        /following-sibling::tr[@style="display: none"]
            [./td[@colspan="2"]/input
                [@type="hidden"]
                [@id="name__token"]
            ]
    ]
    [count(.//input)=3]
'
        );
    }

    public function testRepeatedWithCustomOptions()
    {
        $form = $this->factory->createNamed('repeated', 'name', 'foobar', array(
            'type'           => 'password',
            'first_options'  => array('label' => 'Test', 'required' => false),
            'second_options' => array('label' => 'Test2')
        ));

        $this->assertWidgetMatchesXpath($form->createView(), array(),
'/table
    [
        ./tr
            [
                ./td
                    [./label[@for="name_first"][.="[trans]Test[/trans]"]]
                /following-sibling::td
                    [./input[@type="password"][@id="name_first"][@required="required"]]
            ]
        /following-sibling::tr
            [
                ./td
                    [./label[@for="name_second"][.="[trans]Test2[/trans]"]]
                /following-sibling::td
                    [./input[@type="password"][@id="name_second"][@required="required"]]
            ]
        /following-sibling::tr[@style="display: none"]
            [./td[@colspan="2"]/input
                [@type="hidden"]
                [@id="name__token"]
            ]
    ]
    [count(.//input)=3]
'
        );
    }
}
