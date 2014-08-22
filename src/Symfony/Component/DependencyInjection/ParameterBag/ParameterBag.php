<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\ParameterBag;

use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Exception\ParameterCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * Holds parameters.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class ParameterBag implements ParameterBagInterface
{
    protected $parameters = array();
    protected $resolved = false;
    private $resolvedParameters = array();

    /**
     * Constructor.
     *
     * @param array $parameters An array of parameters
     *
     * @api
     */
    public function __construct(array $parameters = array())
    {
        $this->add($parameters);
    }

    /**
     * Clears all parameters.
     *
     * @api
     */
    public function clear()
    {
        $this->parameters = array();
        $this->resolvedParameters = array();
    }

    /**
     * Adds parameters to the service container parameters.
     *
     * @param array $parameters An array of parameters
     *
     * @api
     */
    public function add(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $this->parameters[strtolower($key)] = $value;
        }

        $this->resolvedParameters = array();
    }

    /**
     * Gets the service container parameters.
     *
     * @return array An array of parameters
     *
     * @api
     */
    public function all()
    {
        return $this->parameters;
    }

    /**
     * Gets a service container parameter.
     *
     * @param string $name The parameter name
     *
     * @return mixed  The parameter value
     *
     * @throws ParameterNotFoundException if the parameter is not defined
     *
     * @api
     */
    public function get($name)
    {
        $name = strtolower($name);

        if (!array_key_exists($name, $this->parameters)) {
            if (!$name) {
                throw new ParameterNotFoundException($name);
            }

            $alternatives = array();
            foreach (array_keys($this->parameters) as $key) {
                $lev = levenshtein($name, $key);
                if ($lev <= strlen($name) / 3 || false !== strpos($key, $name)) {
                    $alternatives[] = $key;
                }
            }

            throw new ParameterNotFoundException($name, null, null, null, $alternatives);
        }

        return $this->parameters[$name];
    }

    /**
     * Sets a service container parameter.
     *
     * @param string $name  The parameter name
     * @param mixed  $value The parameter value
     *
     * @api
     */
    public function set($name, $value)
    {
        $this->parameters[strtolower($name)] = $value;
        $this->resolvedParameters = array();
    }

    /**
     * Returns true if a parameter name is defined.
     *
     * @param string $name The parameter name
     *
     * @return bool    true if the parameter name is defined, false otherwise
     *
     * @api
     */
    public function has($name)
    {
        return array_key_exists(strtolower($name), $this->parameters);
    }

    /**
     * Removes a parameter.
     *
     * @param string $name The parameter name
     *
     * @api
     */
    public function remove($name)
    {
        unset($this->parameters[strtolower($name)]);
        $this->resolvedParameters = array();
    }

    /**
     * Replaces parameter placeholders (%name%) by their values for all parameters.
     */
    public function resolve()
    {
        if ($this->resolved) {
            return;
        }

        $parameters = array();
        foreach ($this->parameters as $key => $value) {
            try {
                $value = $this->resolveParameter($key);
                $parameters[$key] = $this->unescapeValue($value);
            } catch (ParameterNotFoundException $e) {
                $e->setSourceKey($key);

                throw $e;
            }
        }

        $this->parameters = $parameters;
        $this->resolved = true;
        $this->resolvedParameters = array();
    }

    /**
     * Replaces parameter placeholders (%name%) by their values, inside a parameter's value.
     *
     * @param string $name The parameter name whose value we want to resolve
     * @param array $resolving An array of keys that are being resolved (used internally to detect circular references)
     *
     * @return mixed  The resolved parameter value
     *
     * @throws ParameterNotFoundException if a placeholder references a parameter that does not exist
     * @throws ParameterCircularReferenceException if a circular reference if detected
     * @throws RuntimeException when a given parameter has a type problem.
     *
     * @internal this method is public because it's called internally by a callback (calling private methods requires PHP 5.4+), do not use it explicitly in your code
     */
    public function resolveParameter($name, array $resolving = array())
    {
        if ($this->resolved) {
            return $this->get($name);
        }

        if (isset($this->resolvedParameters[$name])) {
            return $this->resolvedParameters[$name];
        }

        return $this->resolvedParameters[$name] = $this->resolveValue($this->get($name), $resolving);
    }

    /**
     * Replaces parameter placeholders (%name%) by their values.
     *
     * @param mixed $value     A value
     * @param array $resolving An array of keys that are being resolved (used internally to detect circular references)
     *
     * @return mixed The resolved value
     *
     * @throws ParameterNotFoundException if a placeholder references a parameter that does not exist
     * @throws ParameterCircularReferenceException if a circular reference if detected
     * @throws RuntimeException when a given parameter has a type problem.
     */
    public function resolveValue($value, array $resolving = array())
    {
        if (is_array($value)) {
            $args = array();
            foreach ($value as $k => $v) {
                $args[$this->resolveValue($k, $resolving)] = $this->resolveValue($v, $resolving);
            }

            return $args;
        }

        if (!is_string($value)) {
            return $value;
        }

        return $this->resolveString($value, $resolving);
    }

    /**
     * Resolves parameters inside a string
     *
     * @param string $value     The string to resolve
     * @param array  $resolving An array of keys that are being resolved (used internally to detect circular references)
     *
     * @return string The resolved string
     *
     * @throws ParameterNotFoundException if a placeholder references a parameter that does not exist
     * @throws ParameterCircularReferenceException if a circular reference if detected
     * @throws RuntimeException when a given parameter has a type problem.
     */
    public function resolveString($value, array $resolving = array())
    {
        // we do this to deal with non string values (Boolean, integer, ...)
        // as the preg_replace_callback throw an exception when trying
        // a non-string in a parameter value
        if (preg_match('/^%([^%\s]+)%$/', $value, $match)) {

            $key = strtolower($match[1]);
            if (isset($resolving[$key])) {
                throw new ParameterCircularReferenceException(array_keys($resolving));
            }

            $resolving[$key] = true;

            return $this->resolveParameter($key, $resolving);
        }

        $self = $this;

        return preg_replace_callback('/%%|%([^%\s]+)%/', function ($match) use ($self, $resolving, $value) {
            // skip %%
            if (!isset($match[1])) {
                return '%%';
            }

            $key = strtolower($match[1]);
            if (isset($resolving[$key])) {
                throw new ParameterCircularReferenceException(array_keys($resolving));
            }

            $paramValue = $self->get($key);

            if (!is_string($paramValue) && !is_numeric($paramValue)) {
                throw new RuntimeException(sprintf('A string value must be composed of strings and/or numbers, but found parameter "%s" of type %s inside string value "%s".', $key, gettype($paramValue), $value));
            }

            $resolving[$key] = true;

            return (string) $self->resolveParameter($key, $resolving);
        }, $value);
    }

    public function isResolved()
    {
        return $this->resolved;
    }

    /**
     * {@inheritdoc}
     */
    public function escapeValue($value)
    {
        if (is_string($value)) {
            return str_replace('%', '%%', $value);
        }

        if (is_array($value)) {
            $result = array();
            foreach ($value as $k => $v) {
                $result[$k] = $this->escapeValue($v);
            }

            return $result;
        }

        return $value;
    }

    public function unescapeValue($value)
    {
        if (is_string($value)) {
            return str_replace('%%', '%', $value);
        }

        if (is_array($value)) {
            $result = array();
            foreach ($value as $k => $v) {
                $result[$k] = $this->unescapeValue($v);
            }

            return $result;
        }

        return $value;
    }
}
