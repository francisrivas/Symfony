<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Tests\ChoiceList\Factory;

use Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class PropertyAccessDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $decoratedFactory;

    /**
     * @var PropertyAccessDecorator
     */
    private $factory;

    protected function setUp()
    {
        $this->decoratedFactory = $this->getMock('Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface');
        $this->factory = new PropertyAccessDecorator($this->decoratedFactory);
    }

    public function testCreateFromChoicesPropertyPath()
    {
        $choices = array((object) array('property' => 'value'));

        $this->decoratedFactory->expects($this->once())
            ->method('createListFromChoices')
            ->with($choices, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($choices, $callback) {
                return array_map($callback, $choices);
            }));

        $this->assertSame(array('value'), $this->factory->createListFromChoices($choices, 'property'));
    }

    public function testCreateFromChoicesPropertyPathInstance()
    {
        $choices = array((object) array('property' => 'value'));

        $this->decoratedFactory->expects($this->once())
            ->method('createListFromChoices')
            ->with($choices, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($choices, $callback) {
                return array_map($callback, $choices);
            }));

        $this->assertSame(array('value'), $this->factory->createListFromChoices($choices, new PropertyPath('property')));
    }

    public function testCreateFromFlippedChoices()
    {
        // Property paths are not supported here, because array keys can never
        // be objects anyway
        $choices = array('a' => 'A');
        $value = 'foobar';
        $list = new \stdClass();

        $this->decoratedFactory->expects($this->once())
            ->method('createListFromFlippedChoices')
            ->with($choices, $value)
            ->will($this->returnValue($list));

        $this->assertSame($list, $this->factory->createListFromFlippedChoices($choices, $value));
    }

    public function testCreateFromLoaderPropertyPath()
    {
        $loader = $this->getMock('Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface');

        $this->decoratedFactory->expects($this->once())
            ->method('createListFromLoader')
            ->with($loader, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($loader, $callback) {
                return $callback((object) array('property' => 'value'));
            }));

        $this->assertSame('value', $this->factory->createListFromLoader($loader, 'property'));
    }

    public function testCreateFromLoaderPropertyPathInstance()
    {
        $loader = $this->getMock('Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface');

        $this->decoratedFactory->expects($this->once())
            ->method('createListFromLoader')
            ->with($loader, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($loader, $callback) {
                return $callback((object) array('property' => 'value'));
            }));

        $this->assertSame('value', $this->factory->createListFromLoader($loader, new PropertyPath('property')));
    }

    public function testCreateViewPreferredChoicesAsPropertyPath()
    {
        $list = $this->getMock('Symfony\Component\Form\ChoiceList\ChoiceListInterface');

        $this->decoratedFactory->expects($this->once())
            ->method('createView')
            ->with($list, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($list, $preferred) {
                return $preferred((object) array('property' => true));
            }));

        $this->assertTrue($this->factory->createView(
            $list,
            'property'
        ));
    }

    public function testCreateViewPreferredChoicesAsPropertyPathInstance()
    {
        $list = $this->getMock('Symfony\Component\Form\ChoiceList\ChoiceListInterface');

        $this->decoratedFactory->expects($this->once())
            ->method('createView')
            ->with($list, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($list, $preferred) {
                return $preferred((object) array('property' => true));
            }));

        $this->assertTrue($this->factory->createView(
            $list,
            new PropertyPath('property')
        ));
    }

    // https://github.com/symfony/symfony/issues/5494
    public function testCreateViewAssumeNullIfPreferredChoicesPropertyPathUnreadable()
    {
        $list = $this->getMock('Symfony\Component\Form\ChoiceList\ChoiceListInterface');

        $this->decoratedFactory->expects($this->once())
            ->method('createView')
            ->with($list, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($list, $preferred) {
                return $preferred((object) array('category' => null));
            }));

        $this->assertFalse($this->factory->createView(
            $list,
            'category.preferred'
        ));
    }

    public function testCreateViewLabelsAsPropertyPath()
    {
        $list = $this->getMock('Symfony\Component\Form\ChoiceList\ChoiceListInterface');

        $this->decoratedFactory->expects($this->once())
            ->method('createView')
            ->with($list, null, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($list, $preferred, $label) {
                return $label((object) array('property' => 'label'));
            }));

        $this->assertSame('label', $this->factory->createView(
            $list,
            null, // preferred choices
            'property'
        ));
    }

    public function testCreateViewLabelsAsPropertyPathInstance()
    {
        $list = $this->getMock('Symfony\Component\Form\ChoiceList\ChoiceListInterface');

        $this->decoratedFactory->expects($this->once())
            ->method('createView')
            ->with($list, null, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($list, $preferred, $label) {
                return $label((object) array('property' => 'label'));
            }));

        $this->assertSame('label', $this->factory->createView(
            $list,
            null, // preferred choices
            new PropertyPath('property')
        ));
    }

    public function testCreateViewIndicesAsPropertyPath()
    {
        $list = $this->getMock('Symfony\Component\Form\ChoiceList\ChoiceListInterface');

        $this->decoratedFactory->expects($this->once())
            ->method('createView')
            ->with($list, null, null, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($list, $preferred, $label, $index) {
                return $index((object) array('property' => 'index'));
            }));

        $this->assertSame('index', $this->factory->createView(
            $list,
            null, // preferred choices
            null, // label
            'property'
        ));
    }

    public function testCreateViewIndicesAsPropertyPathInstance()
    {
        $list = $this->getMock('Symfony\Component\Form\ChoiceList\ChoiceListInterface');

        $this->decoratedFactory->expects($this->once())
            ->method('createView')
            ->with($list, null, null, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($list, $preferred, $label, $index) {
                return $index((object) array('property' => 'index'));
            }));

        $this->assertSame('index', $this->factory->createView(
            $list,
            null, // preferred choices
            null, // label
            new PropertyPath('property')
        ));
    }

    public function testCreateViewGroupsAsPropertyPath()
    {
        $list = $this->getMock('Symfony\Component\Form\ChoiceList\ChoiceListInterface');

        $this->decoratedFactory->expects($this->once())
            ->method('createView')
            ->with($list, null, null, null, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($list, $preferred, $label, $index, $groupBy) {
                return $groupBy((object) array('property' => 'group'));
            }));

        $this->assertSame('group', $this->factory->createView(
            $list,
            null, // preferred choices
            null, // label
            null, // index
            'property'
        ));
    }

    public function testCreateViewGroupsAsPropertyPathInstance()
    {
        $list = $this->getMock('Symfony\Component\Form\ChoiceList\ChoiceListInterface');

        $this->decoratedFactory->expects($this->once())
            ->method('createView')
            ->with($list, null, null, null, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($list, $preferred, $label, $index, $groupBy) {
                return $groupBy((object) array('property' => 'group'));
            }));

        $this->assertSame('group', $this->factory->createView(
            $list,
            null, // preferred choices
            null, // label
            null, // index
            new PropertyPath('property')
        ));
    }

    // https://github.com/symfony/symfony/issues/5494
    public function testCreateViewAssumeNullIfGroupsPropertyPathUnreadable()
    {
        $list = $this->getMock('Symfony\Component\Form\ChoiceList\ChoiceListInterface');

        $this->decoratedFactory->expects($this->once())
            ->method('createView')
            ->with($list, null, null, null, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($list, $preferred, $label, $index, $groupBy) {
                return $groupBy((object) array('group' => null));
            }));

        $this->assertNull($this->factory->createView(
            $list,
            null, // preferred choices
            null, // label
            null, // index
            'group.name'
        ));
    }

    public function testCreateViewAttrAsPropertyPath()
    {
        $list = $this->getMock('Symfony\Component\Form\ChoiceList\ChoiceListInterface');

        $this->decoratedFactory->expects($this->once())
            ->method('createView')
            ->with($list, null, null, null, null, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($list, $preferred, $label, $index, $groupBy, $attr) {
                return $attr((object) array('property' => 'attr'));
            }));

        $this->assertSame('attr', $this->factory->createView(
            $list,
            null, // preferred choices
            null, // label
            null, // index
            null, // groups
            'property'
        ));
    }

    public function testCreateViewLabelAttrAsPropertyPath()
    {
        $list = $this->getMock('Symfony\Component\Form\ChoiceList\ChoiceListInterface');

        $this->decoratedFactory->expects($this->once())
            ->method('createView')
            ->with($list, null, null, null, null, null, null, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($list, $preferred, $label, $index, $groupBy, $attr, $labelAttr) {
                return $labelAttr((object) array('property' => 'label_attr'));
            }));

        $this->assertSame('label_attr', $this->factory->createView(
            $list,
            null, // preferred choices
            null, // label
            null, // index
            null, // groups
            null, // attr
            'property'
        ));
    }

    public function testCreateViewAttrAsPropertyPathInstance()
    {
        $list = $this->getMock('Symfony\Component\Form\ChoiceList\ChoiceListInterface');

        $this->decoratedFactory->expects($this->once())
            ->method('createView')
            ->with($list, null, null, null, null, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($list, $preferred, $label, $index, $groupBy, $attr) {
                return $attr((object) array('property' => 'attr'));
            }));

        $this->assertSame('attr', $this->factory->createView(
            $list,
            null, // preferred choices
            null, // label
            null, // index
            null, // groups
            new PropertyPath('property')
        ));
    }

    public function testCreateViewLabelAttrAsPropertyPathInstance()
    {
        $list = $this->getMock('Symfony\Component\Form\ChoiceList\ChoiceListInterface');

        $this->decoratedFactory->expects($this->once())
            ->method('createView')
            ->with($list, null, null, null, null, null, $this->isInstanceOf('\Closure'))
            ->will($this->returnCallback(function ($list, $preferred, $label, $index, $groupBy, $attr, $labelAttr) {
                return $labelAttr((object) array('property' => 'label_attr'));
            }));

        $this->assertSame('label_attr', $this->factory->createView(
            $list,
            null, // preferred choices
            null, // label
            null, // index
            null, // groups
            null, // attr
            new PropertyPath('property')
        ));
    }
}
