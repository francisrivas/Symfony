<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Tests\Compiler;

use Symfony\Component\DependencyInjection\Compiler\AutowiringPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class AutowiringPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();

        $container->register('foo', __NAMESPACE__.'\Foo');
        $container->register('bar', __NAMESPACE__.'\Bar');

        $pass = new AutowiringPass();
        $pass->process($container);

        $this->assertCount(1, $container->getDefinition('bar')->getArguments());
        $this->assertEquals('foo', (string) $container->getDefinition('bar')->getArgument(0));
    }

    public function testProcessAutowireParent()
    {
        $container = new ContainerBuilder();

        $container->register('b', __NAMESPACE__.'\B');
        $container->register('c', __NAMESPACE__.'\C');

        $pass = new AutowiringPass();
        $pass->process($container);

        $this->assertCount(1, $container->getDefinition('c')->getArguments());
        $this->assertEquals('b', (string) $container->getDefinition('c')->getArgument(0));
    }

    public function testProcessAutowireInterface()
    {
        $container = new ContainerBuilder();

        $container->register('f', __NAMESPACE__.'\F');
        $container->register('g', __NAMESPACE__.'\G');

        $pass = new AutowiringPass();
        $pass->process($container);

        $this->assertCount(2, $container->getDefinition('g')->getArguments());
        $this->assertEquals('f', (string) $container->getDefinition('g')->getArgument(0));
        $this->assertEquals('f', (string) $container->getDefinition('g')->getArgument(1));
    }

    public function testCompleteExistingDefinition()
    {
        $container = new ContainerBuilder();

        $container->register('b', __NAMESPACE__.'\B');
        $container->register('f', __NAMESPACE__.'\F');
        $container->register('h', __NAMESPACE__.'\H')->addArgument(new Reference('b'));

        $pass = new AutowiringPass();
        $pass->process($container);

        $this->assertCount(2, $container->getDefinition('h')->getArguments());
        $this->assertEquals('b', (string) $container->getDefinition('h')->getArgument(0));
        $this->assertEquals('f', (string) $container->getDefinition('h')->getArgument(1));
    }

    public function testCompleteExistingDefinitionWithNotDefinedArguments()
    {
        $container = new ContainerBuilder();

        $container->register('b', __NAMESPACE__.'\B');
        $container->register('f', __NAMESPACE__.'\F');
        $container->register('h', __NAMESPACE__.'\H')->addArgument('')->addArgument('');

        $pass = new AutowiringPass();
        $pass->process($container);

        $this->assertCount(2, $container->getDefinition('h')->getArguments());
        $this->assertEquals('b', (string) $container->getDefinition('h')->getArgument(0));
        $this->assertEquals('f', (string) $container->getDefinition('h')->getArgument(1));
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessage Unable to autowire argument of type "Symfony\Component\DependencyInjection\Tests\Compiler\CollisionInterface" for the service "a".
     */
    public function testTypeCollision()
    {
        $container = new ContainerBuilder();

        $container->register('c1', __NAMESPACE__.'\CollisionA');
        $container->register('c2', __NAMESPACE__.'\CollisionB');
        $container->register('a', __NAMESPACE__.'\CannotBeAutowired');

        $pass = new AutowiringPass();
        $pass->process($container);
    }

    public function testWithTypeSet()
    {
        $container = new ContainerBuilder();

        $container->register('c1', __NAMESPACE__.'\CollisionA');
        $container->register('c2', __NAMESPACE__.'\CollisionB')->addType(__NAMESPACE__.'\CollisionInterface');
        $container->register('a', __NAMESPACE__.'\CannotBeAutowired');

        $pass = new AutowiringPass();
        $pass->process($container);

        $this->assertCount(1, $container->getDefinition('a')->getArguments());
        $this->assertEquals('c2', (string) $container->getDefinition('a')->getArgument(0));
    }

    public function testCreateDefinition()
    {
        $container = new ContainerBuilder();

        $container->register('coop_tilleuls', __NAMESPACE__.'\LesTilleuls');

        $pass = new AutowiringPass();
        $pass->process($container);

        $this->assertCount(1, $container->getDefinition('coop_tilleuls')->getArguments());
        $this->assertEquals('autowired.symfony\component\dependencyinjection\tests\compiler\dunglas', $container->getDefinition('coop_tilleuls')->getArgument(0));

        $dunglasDefinition = $container->getDefinition('autowired.symfony\component\dependencyinjection\tests\compiler\dunglas');
        $this->assertEquals(__NAMESPACE__.'\Dunglas', $dunglasDefinition->getClass());
        $this->assertFalse($dunglasDefinition->isPublic());
        $this->assertCount(1, $dunglasDefinition->getArguments());
        $this->assertEquals('autowired.symfony\component\dependencyinjection\tests\compiler\lille', $dunglasDefinition->getArgument(0));

        $lilleDefinition = $container->getDefinition('autowired.symfony\component\dependencyinjection\tests\compiler\lille');
        $this->assertEquals(__NAMESPACE__.'\Lille', $lilleDefinition->getClass());
    }

    public function testResolveParameter()
    {
        $container = new ContainerBuilder();

        $container->setParameter('class_name', __NAMESPACE__.'\Foo');
        $container->register('foo', '%class_name%');
        $container->register('bar', __NAMESPACE__.'\Bar');

        $pass = new AutowiringPass();
        $pass->process($container);

        $this->assertEquals('foo', $container->getDefinition('bar')->getArgument(0));
    }

    public function testOptionalParameter()
    {
        $container = new ContainerBuilder();

        $container->register('a', __NAMESPACE__.'\A');
        $container->register('foo', __NAMESPACE__.'\Foo');
        $container->register('opt', __NAMESPACE__.'\OptionalParameter');

        $pass = new AutowiringPass();
        $pass->process($container);

        $definition = $container->getDefinition('opt');
        $this->assertNull($definition->getArgument(0));
        $this->assertEquals('a', $definition->getArgument(1));
        $this->assertEquals('foo', $definition->getArgument(2));
    }

    public function testNoAutowiringTag()
    {
        $container = new ContainerBuilder();

        $container->register('foo', __NAMESPACE__.'\Foo');
        $barDefintion = $container->register('bar', __NAMESPACE__.'\Bar');
        $barDefintion->addTag(AutowiringPass::NO_AUTOWIRING);

        $pass = new AutowiringPass();
        $pass->process($container);

        $this->assertCount(0, $container->getDefinition('bar')->getArguments());
    }
}

class Foo
{
}

class Bar
{
    public function __construct(Foo $foo)
    {
    }
}

class A
{
}

class B extends A
{
}

class C
{
    public function __construct(A $a)
    {
    }
}

interface DInterface
{
}

interface EInterface extends DInterface
{
}

class F implements EInterface
{
}

class G
{
    public function __construct(DInterface $d, EInterface $e)
    {
    }
}

class H
{
    public function __construct(B $b, DInterface $d)
    {
    }
}

interface CollisionInterface
{
}

class CollisionA implements CollisionInterface
{
}

class CollisionB implements CollisionInterface
{
}

class CannotBeAutowired
{
    public function __construct(CollisionInterface $collision)
    {
    }
}

class Lille
{
}

class Dunglas
{
    public function __construct(Lille $l)
    {
    }
}

class LesTilleuls
{
    public function __construct(Dunglas $k)
    {
    }
}

class OptionalParameter
{
    public function __construct(CollisionInterface $c = null, A $a, Foo $f = null)
    {
    }
}
