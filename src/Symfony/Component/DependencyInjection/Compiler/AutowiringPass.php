<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Guesses constructor arguments of services definitions and try to instantiate services if necessary.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class AutowiringPass implements CompilerPassInterface
{
    private $container;
    private $reflectionClasses = array();
    private $definedTypes = array();
    private $types;
    private $notGuessableTypes = array();

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->container = $container;
        foreach ($container->getDefinitions() as $id => $definition) {
            $this->completeDefinition($id, $definition);
        }

        // Free memory and remove circular reference to container
        $this->container = null;
        $this->reflectionClasses = array();
        $this->definedTypes = array();
        $this->types = null;
        $this->notGuessableTypes = array();
    }

    /**
     * Wires the given definition.
     *
     * @param string     $id
     * @param Definition $definition
     *
     * @throws RuntimeException
     */
    private function completeDefinition($id, Definition $definition)
    {
        if (!($reflectionClass = $this->getReflectionClass($id, $definition))) {
            return;
        }

        if (!($constructor = $reflectionClass->getConstructor())) {
            return;
        }

        $arguments = $definition->getArguments();
        foreach ($constructor->getParameters() as $index => $parameter) {
            if (!($typeHint = $parameter->getClass())) {
                continue;
            }

            $argumentExist = array_key_exists($index, $arguments);
            if ($argumentExist && '' !== $arguments[$index]) {
                continue;
            }

            if (null === $this->types) {
                $this->populateAvailableTypes();
            }

            if (isset($this->types[$typeHint->name])) {
                $value = new Reference($this->types[$typeHint->name]);
            } else {
                try {
                    $value = $this->createAutowiredDefinition($typeHint);
                } catch (RuntimeException $e) {
                    if (!$parameter->isDefaultValueAvailable()) {
                        throw $e;
                    }

                    $value = $parameter->getDefaultValue();
                }
            }

            if ($argumentExist) {
                $definition->replaceArgument($index, $value);
            } else {
                $definition->addArgument($value);
            }
        }
    }

    /**
     * Populates the list of available types.
     */
    private function populateAvailableTypes()
    {
        $this->types = array();

        foreach ($this->container->getDefinitions() as $id => $definition) {
            $this->populateAvailableType($id, $definition);
        }
    }

    /**
     * Populates the of available types for a given definition.
     *
     * @param string     $id
     * @param Definition $definition
     */
    private function populateAvailableType($id, Definition $definition)
    {
        if (!($class = $definition->getClass())) {
            return;
        }

        foreach ($definition->getTypes() as $type) {
            $this->definedTypes[$type] = true;
            $this->types[$type] = $id;
        }

        if ($reflectionClass = $this->getReflectionClass($id, $definition)) {
            $this->extractInterfaces($id, $reflectionClass);
            $this->extractAncestors($id, $reflectionClass);
        }
    }

    /**
     * Extracts the list of all interfaces implemented by a class.
     *
     * @param string           $id
     * @param \ReflectionClass $reflectionClass
     */
    private function extractInterfaces($id, \ReflectionClass $reflectionClass)
    {
        foreach ($reflectionClass->getInterfaces() as $interfaceName => $reflectionInterface) {
            $this->set($interfaceName, $id);

            $this->extractInterfaces($id, $reflectionInterface);
        }
    }

    /**
     * Extracts all inherited types of a class.
     *
     * @param string           $id
     * @param \ReflectionClass $reflectionClass
     */
    private function extractAncestors($id, \ReflectionClass $reflectionClass)
    {
        $this->set($reflectionClass->name, $id);

        if ($reflectionParentClass = $reflectionClass->getParentClass()) {
            $this->extractAncestors($id, $reflectionParentClass);
        }
    }

    /**
     * Associates if applicable a type and a service id or a class.
     *
     * @param string $type
     * @param string $value A service id or a class name depending of the value of $class
     */
    private function set($type, $value)
    {
        if (isset($this->definedTypes[$type]) || isset($this->notGuessableTypes[$type])) {
            return;
        }

        if (isset($this->types[$type])) {
            if ($this->types[$type] === $value) {
                return;
            }

            unset($this->types[$type]);
            $this->notGuessableTypes[$type] = true;

            return;
        }

        $this->types[$type] = $value;
    }

    /**
     * Registers a definition for the type if possible or throws an exception.
     *
     * @param \ReflectionClass $typeHint
     *
     * @return Reference A reference to the registered definition
     *
     * @throws RuntimeException
     */
    private function createAutowiredDefinition(\ReflectionClass $typeHint)
    {
        if (!$typeHint->isInstantiable()) {
            throw new RuntimeException(sprintf('Unable to autowire type "%s".', $typeHint->name));
        }

        $argumentId = sprintf('autowired.%s', $typeHint->name);

        $argumentDefinition = $this->container->register($argumentId, $typeHint->name);
        $argumentDefinition->setPublic(false);

        $this->populateAvailableType($argumentId, $argumentDefinition);
        $this->completeDefinition($argumentId, $argumentDefinition);

        return new Reference($argumentId);
    }

    /**
     * Retrieves the reflection class associated with the given service.
     *
     * @param string     $id
     * @param Definition $definition
     *
     * @return \ReflectionClass|null
     */
    private function getReflectionClass($id, Definition $definition)
    {
        if (isset($this->reflectionClasses[$id])) {
            return $this->reflectionClasses[$id];
        }

        if (!$class = $definition->getClass()) {
            return;
        }

        $class = $this->container->getParameterBag()->resolveValue($class);

        try {
            return $this->reflectionClasses[$id] = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            // Skip invalid classes definitions to keep BC
        }
    }
}
