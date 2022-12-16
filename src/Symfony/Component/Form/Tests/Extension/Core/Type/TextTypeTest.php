<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Tests\Extension\Core\Type;

use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

class TextTypeTest extends BaseTypeTest
{
    use ExpectDeprecationTrait;

    public const TESTED_TYPE = 'Symfony\Component\Form\Extension\Core\Type\TextType';
    public const TESTED_TYPE_OPTIONS = [
        'empty_data' => null,
    ];

    public function testSubmitNull($expected = null, $norm = null, $view = null)
    {
        parent::testSubmitNull($expected, $norm, '');
    }

    public function testSubmitNullReturnsNullWithEmptyDataAsString()
    {
        $form = $this->factory->create(static::TESTED_TYPE, null, [
            'empty_data' => '',
        ]);

        $form->submit(null);
        $this->assertSame('', $form->getData());
        $this->assertSame('', $form->getNormData());
        $this->assertSame('', $form->getViewData());
    }

    /**
     * @group legacy
     */
    public function testDefaultEmptyDataCallback()
    {
        $this->expectDeprecation('Since symfony/form 6.1: The default value of "empty_data" option in "Symfony\Component\Form\Extension\Core\Type\TextType" will be changed to empty string. Declare "NULL" as value for "empty_data" if you still want use "NULL" as data.');

        $form = $this->factory->create(static::TESTED_TYPE);

        $form->submit(null);
        $this->assertNull($form->getData());
        $this->assertNull($form->getNormData());
        $this->assertSame('', $form->getViewData());
    }

    public function provideZeros()
    {
        return [
            [0, '0'],
            ['0', '0'],
            ['00000', '00000'],
        ];
    }

    /**
     * @dataProvider provideZeros
     *
     * @see https://github.com/symfony/symfony/issues/1986
     */
    public function testSetDataThroughParamsWithZero($data, $dataAsString)
    {
        $form = $this->factory->create(static::TESTED_TYPE, null, $this->getTestedTypeOptions() + [
            'data' => $data,
        ]);
        $view = $form->createView();

        $this->assertFalse($form->isEmpty());

        $this->assertSame($dataAsString, $view->vars['value']);
        $this->assertSame($dataAsString, $form->getData());
    }
}
