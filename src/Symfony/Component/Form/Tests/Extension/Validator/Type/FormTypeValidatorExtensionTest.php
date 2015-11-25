<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Tests\Extension\Validator\Type;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FormTypeValidatorExtensionTest extends BaseValidatorExtensionTest
{
    public function testSubmitValidatesData()
    {
        $builder = $this->factory->createBuilder(
            FormType::class,
            null,
            array(
                'validation_groups' => 'group',
            )
        );
        $builder->add('firstName', FormType::class);
        $form = $builder->getForm();

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($form))
            ->will($this->returnValue(new ConstraintViolationList()));

        // specific data is irrelevant
        $form->submit(array());
    }

    public function testValidConstraint()
    {
        $form = $this->createForm(array('constraints' => $valid = new Valid()));

        $this->assertSame(array($valid), $form->getConfig()->getOption('constraints'));
    }

    public function testValidatorInterface()
    {
        $validator = $this->getMock(ValidatorInterface::class);

        $formTypeValidatorExtension = new FormTypeValidatorExtension($validator);
        $this->assertAttributeSame($validator, 'validator', $formTypeValidatorExtension);
    }

    protected function createForm(array $options = array())
    {
        return $this->factory->create(FormType::class, null, $options);
    }
}
