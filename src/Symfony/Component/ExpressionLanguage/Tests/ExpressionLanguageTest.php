<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\ExpressionLanguage\Tests;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionLanguageTest extends \PHPUnit_Framework_TestCase
{
    public function testCachedParse()
    {
        $cacheMock = $this->getMock('Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface');
        $savedParsedExpression = null;
        $expressionLanguage = new ExpressionLanguage($cacheMock);

        $cacheMock
            ->expects($this->exactly(2))
            ->method('fetch')
            ->with('1 + 1//')
            ->will($this->returnCallback(function () use (&$savedParsedExpression) {
                return $savedParsedExpression;
            }))
        ;
        $cacheMock
            ->expects($this->exactly(1))
            ->method('save')
            ->with('1 + 1//', $this->isInstanceOf('Symfony\Component\ExpressionLanguage\ParsedExpression'))
            ->will($this->returnCallback(function ($key, $expression) use (&$savedParsedExpression) {
                $savedParsedExpression = $expression;
            }))
        ;

        $parsedExpression = $expressionLanguage->parse('1 + 1', array());
        $this->assertSame($savedParsedExpression, $parsedExpression);

        $parsedExpression = $expressionLanguage->parse('1 + 1', array());
        $this->assertSame($savedParsedExpression, $parsedExpression);
    }

    public function testConstantFunction()
    {
        $expressionLanguage = new ExpressionLanguage();
        $this->assertEquals(PHP_VERSION, $expressionLanguage->evaluate('constant("PHP_VERSION")'));

        $expressionLanguage = new ExpressionLanguage();
        $this->assertEquals('constant("PHP_VERSION")', $expressionLanguage->compile('constant("PHP_VERSION")'));
    }

    /**
     * @dataProvider shortCircuitProviderEvaluate
     */
    public function testShortCircuitOperatorsEvaluate($expression, array $values, $expected)
    {
        $expressionLanguage = new ExpressionLanguage();
        $this->assertEquals($expected, $expressionLanguage->evaluate($expression, $values));
    }

    /**
     * @dataProvider shortCircuitProviderCompile
     */
    public function testShortCircuitOperatorsCompile($expression, array $names, $expected)
    {
        $result = null;
        $expressionLanguage = new ExpressionLanguage();
        eval(sprintf('$result = %s;', $expressionLanguage->compile($expression, $names)));
        $this->assertSame($expected, $result);
    }

    public function testUnknownFunctionHandling()
    {
        $expressionLanguage = new ExpressionLanguage();

        $expressionLanguage->register('*',
            function($name, array $names, array $args) {
                $name = (in_array($name, $names)) ? "\$$name" : $name ;
                return sprintf('%s(%s)', $name, implode(', ', $args));
            },
            function($name, array $values, array $args) {
                if (array_key_exists($name, $values)) {
                    return call_user_func_array($values[$name], $args);
                }
                return call_user_func_array($name, $args);
            }
        );

        // global function

        $expected = 'sha1("foo")';
        $this->assertEquals($expected, $expressionLanguage->compile('sha1("foo")'));

        $expected = '0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33';
        $this->assertEquals($expected, $expressionLanguage->evaluate('sha1("foo")'));

        // local function

        $expected = '$f($x)';
        $this->assertEquals($expected, $expressionLanguage->compile('f(x)', array('x', 'f')));

        $f = function($x) { return $x * $x; };
        $g = function($x) { return $x + 1; };

        $expected = 65;
        $this->assertEquals($expected, $expressionLanguage->evaluate('g(f(x))', array('x' => 8, 'f' => $f, 'g' => $g)));
    }

    public function shortCircuitProviderEvaluate()
    {
        $object = $this->getMockBuilder('stdClass')->setMethods(array('foo'))->getMock();
        $object->expects($this->never())->method('foo');

        return array(
            array('false and object.foo()', array('object' => $object), false),
            array('false && object.foo()', array('object' => $object), false),
            array('true || object.foo()', array('object' => $object), true),
            array('true or object.foo()', array('object' => $object), true),
        );
    }

    public function shortCircuitProviderCompile()
    {
        return array(
            array('false and foo', array('foo' => 'foo'), false),
            array('false && foo', array('foo' => 'foo'), false),
            array('true || foo', array('foo' => 'foo'), true),
            array('true or foo', array('foo' => 'foo'), true),
        );
    }
}
