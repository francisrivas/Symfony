<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Serializer\Tests\Builder\CodeGenerator;

use App\CodeGenerator\_Attribute;
use App\CodeGenerator\_Method;
use App\CodeGenerator\_Property;
use App\CodeGenerator\ClassGenerator;
use PHPUnit\Framework\TestCase;

class ClassGeneratorTest extends TestCase
{
    public function testPerson()
    {
        $generator = new ClassGenerator('Person', 'Test\\CodeGenerator\\Fixtures');

        $generator->addProperty(_Property::create('name')
            ->setVisibility('private')
            ->setType('string')
        );
        $generator->addProperty(_Property::create('age')
            ->setVisibility('private')
            ->setType('int')
        );

        $generator->addMethod(_Method::create('__construct')
            ->addArgument('name', 'string')
            ->addArgument('age', 'int')
            ->setBody(<<<PHP
\$this->name = \$name;
\$this->age = \$age;
PHP
            ));

        $generator->addMethod(_Method::create('getName')
            ->setReturnType('string')
            ->setBody('return $this->name;')
        );

        $generator->addMethod(_Method::create('getAge')
            ->setReturnType('int')
            ->setBody('return $this->age;')
        );

        $output = $generator->toString();
        $this->assertEquals(file_get_contents(__DIR__.'/Fixtures/Person.php'), $output);
    }

    /**
     * Constructor argument promotion.
     */
    public function testCat()
    {
        $generator = new ClassGenerator('Cat', 'Test\\CodeGenerator\\Fixtures');

        $generator->addMethod(_Method::create('__construct')
            ->addArgument('name', 'private string')
            ->addArgument('age', 'private int')
        );

        $generator->addMethod(_Method::create('getName')
            ->setReturnType('string')
            ->setBody('return $this->name;')
        );

        $generator->addMethod(_Method::create('getAge')
            ->setReturnType('int')
            ->setBody('return $this->age;')
        );

        $output = $generator->toString();
        $this->assertEquals(file_get_contents(__DIR__.'/Fixtures/Cat.php'), $output);
    }

    /**
     * Try to flex all our features.
     */
    public function testFull()
    {
        $generator = new ClassGenerator('Full', 'Test\\CodeGenerator\\Fixtures');
        $generator->addImport('Test\\CodeGenerator\\Fixtures\\Cat');
        $generator->addImport('Test\\CodeGenerator\\Fixtures\\Foo');
        $generator->addImport('Test\\CodeGenerator\\Fixtures\\Bar');
        $generator->addImport('Test\\CodeGenerator\\Fixtures\\MyAttribute');
        $generator->setExtends('Cat');
        $generator->addImplements('Foo');
        $generator->addImplements('Bar');

        $generator->setFileComment('This is a fixture class.
We use it for verifying the code generation.');
        $generator->setClassComment(<<<TEXT
Perfect class comment.

It has some lines
TEXT
        );

        $generator->addProperty(_Property::create('name')
            ->setVisibility('private')
            ->setType('string')
        );

        $generator->addMethod(_Method::create('__construct')
            ->addArgument('name', 'string', 'foobar')
            ->setBody(<<<PHP
\$this->name = \$name;
PHP
            ));

        $generator->addMethod(_Method::create('getName')
            ->setReturnType('string')
            ->setBody('return $this->name;')
            ->setComment(<<<TEXT
Returns the name of the cat

@return string
TEXT));

        $generator->addAttribute(_Attribute::create('MyAttribute')
            ->addParameter('name', 'test')
        );

        $output = $generator->toString();
        $this->assertEquals(file_get_contents(__DIR__.'/Fixtures/Full.php'), $output);
    }
}
