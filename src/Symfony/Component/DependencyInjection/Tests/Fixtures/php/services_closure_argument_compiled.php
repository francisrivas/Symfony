<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class ProjectServiceContainer extends Container
{
    protected $parameters = [];

    public function __construct()
    {
        $this->services = $this->privates = [];
        $this->methodMap = [
            'foo' => 'getFooService',
            'service_closure' => 'getServiceClosureService',
            'service_closure_invalid' => 'getServiceClosureInvalidService',
        ];

        $this->aliases = [];
    }

    public function compile(): void
    {
        throw new LogicException('You cannot compile a dumped container that was already compiled.');
    }

    public function isCompiled(): bool
    {
        return true;
    }

    /**
     * Gets the public 'foo' shared service.
     *
     * @return \Foo
     */
    protected function getFooService()
    {
        return $this->services['foo'] = new \Foo();
    }

    /**
     * Gets the public 'service_closure' shared service.
     *
     * @return \Bar
     */
    protected function getServiceClosureService()
    {
        return $this->services['service_closure'] = new \Bar(function () {
            return ($this->services['foo'] ?? ($this->services['foo'] = new \Foo()));
        });
    }

    /**
     * Gets the public 'service_closure_invalid' shared service.
     *
     * @return \Bar
     */
    protected function getServiceClosureInvalidService()
    {
        return $this->services['service_closure_invalid'] = new \Bar(function () {
            return NULL;
        });
    }
}
