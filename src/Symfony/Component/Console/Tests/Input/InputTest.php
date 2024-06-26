<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Tests\Input;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class InputTest extends TestCase
{
    public function testConstructor()
    {
        $input = new ArrayInput(['name' => 'foo'], new InputDefinition([new InputArgument('name')]));
        $this->assertEquals('foo', $input->getArgument('name'), '->__construct() takes a InputDefinition as an argument');
    }

    public function testOptions()
    {
        $input = new ArrayInput(['--name' => 'foo'], new InputDefinition([new InputOption('name')]));
        $this->assertEquals('foo', $input->getOption('name'), '->getOption() returns the value for the given option');

        $input->setOption('name', 'bar');
        $this->assertEquals('bar', $input->getOption('name'), '->setOption() sets the value for a given option');
        $this->assertEquals(['name' => 'bar'], $input->getOptions(), '->getOptions() returns all option values');
        $this->assertEquals(['name' => 'bar'], $input->getRawOptions(), '->getRawOptions() returns all option values');

        $input = new ArrayInput(['--name' => 'foo'], new InputDefinition([new InputOption('name'), new InputOption('bar', '', InputOption::VALUE_OPTIONAL, '', 'default')]));
        $this->assertEquals('default', $input->getOption('bar'), '->getOption() returns the default value for optional options');
        $this->assertEquals(['name' => 'foo', 'bar' => 'default'], $input->getOptions(), '->getOptions() returns all option values, even optional ones');
        $this->assertEquals(['name' => 'foo'], $input->getRawOptions(), '->getRawOptions() returns all option values, excluding optional ones');

        $input = new ArrayInput(['--name' => 'foo', '--bar' => ''], new InputDefinition([new InputOption('name'), new InputOption('bar', '', InputOption::VALUE_OPTIONAL, '', 'default')]));
        $this->assertEquals('', $input->getOption('bar'), '->getOption() returns null for options explicitly passed without value (or an empty value)');
        $this->assertEquals(['name' => 'foo', 'bar' => ''], $input->getOptions(), '->getOptions() returns all option values.');
        $this->assertEquals(['name' => 'foo', 'bar' => ''], $input->getRawOptions(), '->getRawOptions() returns all option values.');

        $input = new ArrayInput(['--name' => 'foo', '--bar' => null], new InputDefinition([new InputOption('name'), new InputOption('bar', '', InputOption::VALUE_OPTIONAL, '', 'default')]));
        $this->assertNull($input->getOption('bar'), '->getOption() returns null for options explicitly passed without value (or an empty value)');
        $this->assertEquals(['name' => 'foo', 'bar' => null], $input->getOptions(), '->getOptions() returns all option values');
        $this->assertEquals(['name' => 'foo', 'bar' => null], $input->getRawOptions(), '->getRawOptions() returns all option values');

        $input = new ArrayInput(['--name' => null], new InputDefinition([new InputOption('name', null, InputOption::VALUE_NEGATABLE)]));
        $this->assertTrue($input->hasOption('name'));
        $this->assertTrue($input->hasOption('no-name'));
        $this->assertTrue($input->getOption('name'));
        $this->assertFalse($input->getOption('no-name'));

        $input = new ArrayInput(['--no-name' => null], new InputDefinition([new InputOption('name', null, InputOption::VALUE_NEGATABLE)]));
        $this->assertFalse($input->getOption('name'));
        $this->assertTrue($input->getOption('no-name'));

        $input = new ArrayInput([], new InputDefinition([new InputOption('name', null, InputOption::VALUE_NEGATABLE)]));
        $this->assertNull($input->getOption('name'));
        $this->assertNull($input->getOption('no-name'));
    }

    public function testSetInvalidOption()
    {
        $input = new ArrayInput(['--name' => 'foo'], new InputDefinition([new InputOption('name'), new InputOption('bar', '', InputOption::VALUE_OPTIONAL, '', 'default')]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "foo" option does not exist.');

        $input->setOption('foo', 'bar');
    }

    public function testGetInvalidOption()
    {
        $input = new ArrayInput(['--name' => 'foo'], new InputDefinition([new InputOption('name'), new InputOption('bar', '', InputOption::VALUE_OPTIONAL, '', 'default')]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "foo" option does not exist.');

        $input->getOption('foo');
    }

    public function testArguments()
    {
        $input = new ArrayInput(['name' => 'foo'], new InputDefinition([new InputArgument('name')]));
        $this->assertEquals('foo', $input->getArgument('name'), '->getArgument() returns the value for the given argument');

        $input->setArgument('name', 'bar');
        $this->assertEquals('bar', $input->getArgument('name'), '->setArgument() sets the value for a given argument');
        $this->assertEquals(['name' => 'bar'], $input->getArguments(), '->getArguments() returns all argument values');
        $this->assertEquals(['name' => 'bar'], $input->getRawArguments(), '->getRawArguments() returns all argument values');
        $this->assertEquals([], $input->getRawArguments(true), '->getRawArguments(true) strips the first argument value from the returned argument values');

        $input = new ArrayInput(['name' => 'foo'], new InputDefinition([new InputArgument('name'), new InputArgument('bar', InputArgument::OPTIONAL, '', 'default')]));
        $this->assertEquals('default', $input->getArgument('bar'), '->getArgument() returns the default value for optional arguments');
        $this->assertEquals(['name' => 'foo', 'bar' => 'default'], $input->getArguments(), '->getArguments() returns all argument values, even optional ones');
        $this->assertEquals(['name' => 'foo'], $input->getRawArguments(), '->getRawArguments() returns all argument values, excluding optional ones');
        $this->assertEquals([], $input->getRawArguments(true), '->getRawArguments(true) strips the first argument value from the returned argument values');
    }

    public function testSetInvalidArgument()
    {
        $input = new ArrayInput(['name' => 'foo'], new InputDefinition([new InputArgument('name'), new InputArgument('bar', InputArgument::OPTIONAL, '', 'default')]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "foo" argument does not exist.');

        $input->setArgument('foo', 'bar');
    }

    public function testGetInvalidArgument()
    {
        $input = new ArrayInput(['name' => 'foo'], new InputDefinition([new InputArgument('name'), new InputArgument('bar', InputArgument::OPTIONAL, '', 'default')]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "foo" argument does not exist.');

        $input->getArgument('foo');
    }

    public function testValidateWithMissingArguments()
    {
        $input = new ArrayInput([]);
        $input->bind(new InputDefinition([new InputArgument('name', InputArgument::REQUIRED)]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "name").');

        $input->validate();
    }

    public function testValidateWithMissingRequiredArguments()
    {
        $input = new ArrayInput(['bar' => 'baz']);
        $input->bind(new InputDefinition([new InputArgument('name', InputArgument::REQUIRED), new InputArgument('bar', InputArgument::OPTIONAL)]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "name").');

        $input->validate();
    }

    public function testValidate()
    {
        $input = new ArrayInput(['name' => 'foo']);
        $input->bind(new InputDefinition([new InputArgument('name', InputArgument::REQUIRED)]));

        $this->assertNull($input->validate());
    }

    public function testSetGetInteractive()
    {
        $input = new ArrayInput([]);
        $this->assertTrue($input->isInteractive(), '->isInteractive() returns whether the input should be interactive or not');
        $input->setInteractive(false);
        $this->assertFalse($input->isInteractive(), '->setInteractive() changes the interactive flag');
    }

    public function testSetGetStream()
    {
        $input = new ArrayInput([]);
        $stream = fopen('php://memory', 'r+', false);
        $input->setStream($stream);
        $this->assertSame($stream, $input->getStream());
    }
}
