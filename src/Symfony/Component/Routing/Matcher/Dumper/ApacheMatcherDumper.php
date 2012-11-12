<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Matcher\Dumper;

use Symfony\Component\Routing\Route;

/**
 * Dumps a set of Apache mod_rewrite rules.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Kris Wallsmith <kris@symfony.com>
 */
class ApacheMatcherDumper extends MatcherDumper
{
    /**
     * Dumps a set of Apache mod_rewrite rules.
     *
     * Available options:
     *
     *  * script_name: The script name (app.php by default)
     *  * base_uri:    The base URI ("" by default)
     *
     * @param array $options An array of options
     *
     * @return string A string to be used as Apache rewrite rules
     *
     * @throws \LogicException When the route regex is invalid
     */
    public function dump(array $options = array())
    {
        $options = array_merge(array(
            'script_name' => 'app.php',
            'base_uri'    => '',
        ), $options);

        $options['script_name'] = self::escape($options['script_name'], ' ', '\\');

        $rules = array("# skip \"real\" requests\nRewriteCond %{REQUEST_FILENAME} -f\nRewriteRule .* - [QSA,L]");
        $methodVars = array();

        foreach ($this->getRoutes()->all() as $name => $route) {
            $rules[] = $this->dumpRoute($name, $route, $options);
            $methodVars = array_merge($methodVars, $this->getRouteMethods($route));
        }

        if (0 < count($methodVars)) {
            $rule = array('# 405 Method Not Allowed');
            $methodVars = array_values(array_unique($methodVars));
            foreach ($methodVars as $i => $methodVar) {
                $rule[] = sprintf('RewriteCond %%{_ROUTING_allow_%s} !-z%s', $methodVar, isset($methodVars[$i + 1]) ? ' [OR]' : '');
            }
            $rule[] = sprintf('RewriteRule .* %s [QSA,L]', $options['script_name']);

            $rules[] = implode("\n", $rule);
        }

        return implode("\n\n", $rules)."\n";
    }

    private function dumpRoute($name, $route, array $options)
    {
        $compiledRoute = $route->compile();

        // prepare the apache regex
        $regex = $this->regexToApacheRegex($compiledRoute->getRegex());
        $regex = '^'.self::escape(preg_quote($options['base_uri']).substr($regex, 1), ' ', '\\');

        $methods = $this->getRouteMethods($route);

        $hasTrailingSlash = (!$methods || in_array('HEAD', $methods)) && '/$' === substr($regex, -2) && '^/$' !== $regex;

        $variables = array('E=_ROUTING_route:'.$name);
        foreach ($compiledRoute->getVariables() as $i => $variable) {
            $variables[] = 'E=_ROUTING_param_'.$variable.':%'.($i + 1);
        }
        foreach ($route->getDefaults() as $key => $value) {
            $variables[] = 'E=_ROUTING_default_'.$key.':'.strtr($value, array(
                ':'  => '\\:',
                '='  => '\\=',
                '\\' => '\\\\',
                ' '  => '\\ ',
            ));
        }
        $variables = implode(',', $variables);

        $rule = array("# $name");

        // method mismatch
        if (0 < count($methods)) {
            $allow = array();
            foreach ($methods as $method) {
                $methodVars[] = $method;
                $allow[] = 'E=_ROUTING_allow_'.$method.':1';
            }

            $rule[] = "RewriteCond %{REQUEST_URI} $regex";
            $rule[] = sprintf("RewriteCond %%{REQUEST_METHOD} !^(%s)$ [NC]", implode('|', $methods));
            $rule[] = sprintf('RewriteRule .* - [S=%d,%s]', $hasTrailingSlash ? 2 : 1, implode(',', $allow));
        }

        // redirect with trailing slash appended
        if ($hasTrailingSlash) {
            $rule[] = 'RewriteCond %{REQUEST_URI} '.substr($regex, 0, -2).'$';
            $rule[] = 'RewriteRule .* $0/ [QSA,L,R=301]';
        }

        // the main rule
        $rule[] = "RewriteCond %{REQUEST_URI} $regex";
        $rule[] = "RewriteRule .* {$options['script_name']} [QSA,L,$variables]";

        return implode("\n", $rule);
    }

    /**
     * Returns methods allowed for a route
     *
     * @param Route  $route The route
     *
     * @return array The methods
     */
    private function getRouteMethods(Route $route)
    {
        $methods = array();
        if ($req = $route->getRequirement('_method')) {
            $methods = explode('|', strtoupper($req));
            // GET and HEAD are equivalent
            if (in_array('GET', $methods) && !in_array('HEAD', $methods)) {
                $methods[] = 'HEAD';
            }
        }

        return $methods;
    }

    /**
     * Converts a regex to make it suitable for mod_rewrite
     *
     * @param string  $regex The regex
     *
     * @return string The converted regex
     */
    private function regexToApacheRegex($regex)
    {
        $delimiter = $regex[0];
        $regexPatternEnd = strrpos($regex, $delimiter);
        if (strlen($regex) < 2 || 0 === $regexPatternEnd) {
            throw new \LogicException('The "%s" route regex "%s" is invalid', $name, $regex);
        }
        $regex = preg_replace('/\?<.+?>/', '', substr($regex, 1, $regexPatternEnd - 1));

        return $regex;
    }

    /**
     * Escapes a string.
     *
     * @param string $string The string to be escaped
     * @param string $char   The character to be escaped
     * @param string $with   The character to be used for escaping
     *
     * @return string The escaped string
     */
    private static function escape($string, $char, $with)
    {
        $escaped = false;
        $output = '';
        foreach (str_split($string) as $symbol) {
            if ($escaped) {
                $output .= $symbol;
                $escaped = false;
                continue;
            }
            if ($symbol === $char) {
                $output .= $with.$char;
                continue;
            }
            if ($symbol === $with) {
                $escaped = true;
            }
            $output .= $symbol;
        }

        return $output;
    }
}
