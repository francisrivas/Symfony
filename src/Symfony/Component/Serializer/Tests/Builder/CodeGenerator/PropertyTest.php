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


use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Builder\CodeGenerator\Property;

class PropertyTest extends TestCase
{
    public function testNameOnly()
    {
        $output = Property::create('foobar')->toString();
        $this->assertEquals('public $foobar;', $output);
    }

    public function testVisibility()
    {
        $output = Property::create('foobar')->setVisibility('private')->toString();
        $this->assertEquals('private $foobar;', $output);

        $output = Property::create('foobar')->setVisibility('protected')->toString();
        $this->assertEquals('protected $foobar;', $output);

        // We dont care about logic
        $output = Property::create('foobar')->setVisibility('crazy')->toString();
        $this->assertEquals('crazy $foobar;', $output);
    }

    public function testType()
    {
        $output = Property::create('foobar')->setType('int')->toString();
        $this->assertEquals('public int $foobar;', $output);

        $output = Property::create('foobar')->setType('mixed')->toString();
        $this->assertEquals('public mixed $foobar;', $output);

        $output = Property::create('foobar')->setType('?string')->toString();
        $this->assertEquals('public ?string $foobar;', $output);
    }

    public function testDefaultValue()
    {
        $output = Property::create('foobar')->setDefaultValue('2')->toString();
        $this->assertEquals('public $foobar = \'2\';', $output);

        $output = Property::create('foobar')->setDefaultValue(2)->toString();
        $this->assertEquals('public $foobar = 2;', $output);

        $output = Property::create('foobar')->setDefaultValue(null)->toString();
        $this->assertEquals('public $foobar = NULL;', $output);
    }

    public function testTypeAndDefaultValue()
    {
        $output = Property::create('foobar')->setType('string')->setDefaultValue('2')->toString();
        $this->assertEquals('public string $foobar = \'2\';', $output);

        $output = Property::create('foobar')->setType('int')->setDefaultValue(2)->toString();
        $this->assertEquals('public int $foobar = 2;', $output);

        $output = Property::create('foobar')->setType('?int')->setDefaultValue(null)->toString();
        $this->assertEquals('public ?int $foobar = NULL;', $output);

        // We dont care about logic here.
        $output = Property::create('foobar')->setType('int')->setDefaultValue('test')->toString();
        $this->assertEquals('public int $foobar = \'test\';', $output);
    }

    public function testComment()
    {
        $output = Property::create('foobar')
            ->setType('string')
            ->setDefaultValue('2')
            ->setComment('This is a comment')
            ->toString();
        $this->assertEquals(<<<PHP
/**
 * This is a comment
 */
public string \$foobar = '2';
PHP, $output);
    }
}
