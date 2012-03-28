<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Matcher;

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ArrayUrlMatcher implements UrlMatcherInterface
{
    protected $context;
    protected $allow;

    private $routes;

    /**
     * Constructor.
     *
     * @param array          $routes  An array of routes as generated by PhpArrayMatcherDumper
     * @param RequestContext $context The context
     *
     * @api
     */
    public function __construct(array $routes, RequestContext $context)
    {
        $this->routes = $routes;
        $this->context = $context;
    }

    /**
     * Sets the request context.
     *
     * @param RequestContext $context The context
     *
     * @api
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    /**
     * Gets the request context.
     *
     * @return RequestContext The context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function match($pathinfo)
    {
        $this->allow = array();
        $pathinfo = urldecode($pathinfo);

        foreach ($this->routes as $prefix => $route) {
            if (!is_int($prefix)) {
                if (0 !== strpos($pathinfo, $prefix)) {
                    continue;
                }

                foreach ($route as $r) {
                    if ($ret = $this->matchRoute($r)) {
                        return $ret;
                    }
                }
            } elseif ($ret = $this->matchRoute($route)) {
                return $ret;
            }
        }

        throw 0 < count($this->allow)
            ? new MethodNotAllowedException(array_unique(array_map('strtoupper', $this->allow)))
            : new ResourceNotFoundException();
    }

    public function matchRoute($route)
    {
        if (isset($route['methods']) && !in_array($this->context->getMethod(), $route['methods'])) {
            $this->allow = array_merge($this->allow, $route['methods']);

            return;
        }

        if (isset($route['scheme']) && $this->context->getScheme() != $route['scheme']) {
            return;
        }

        if (isset($route['static'])) {
            if ($pathinfo === $route['static']) {
                $matches = isset($route['defaults']) ? $route['defaults'] : array();
                $matches['_route'] = $route['name'];

                return $matches;
            }

            return;
        }

        if (preg_match($route['regex'], $pathinfo, $matches)) {
            $matches['_route'] = $route['name'];

            if (isset($route('defaults'))) {
                $matches = $this->mergeDefaults($matches, $route['defaults']);
            }

            return $matches;
        }
    }

    protected function mergeDefaults($params, $defaults)
    {
        $parameters = $defaults;
        foreach ($params as $key => $value) {
            if (!is_int($key)) {
                $parameters[$key] = rawurldecode($value);
            }
        }

        return $parameters;
    }
}
