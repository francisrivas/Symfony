<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\ClassLoader;

/**
 * ClassCollectionLoader.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ClassCollectionLoader
{
    static private $loaded;
    static private $baseClassesCountMap;

    /**
     * Loads a list of classes and caches them in one big file.
     *
     * @param array   $classes    An array of classes to load
     * @param string  $cacheDir   A cache directory
     * @param string  $name       The cache name prefix
     * @param Boolean $autoReload Whether to flush the cache when the cache is stale or not
     * @param Boolean $adaptive   Whether to remove already declared classes or not
     * @param string  $extension  File extension of the resulting file
     *
     * @throws \InvalidArgumentException When class can't be loaded
     */
    static public function load($classes, $cacheDir, $name, $autoReload, $adaptive = false, $extension = '.php')
    {
        // each $name can only be loaded once per PHP process
        if (isset(self::$loaded[$name])) {
            return;
        }

        self::$loaded[$name] = true;

        $declared = array_merge(get_declared_classes(), get_declared_interfaces());
        if (function_exists('get_declared_traits')) {
            $declared = array_merge($declared, get_declared_traits());
        }

        if ($adaptive) {
            // don't include already declared classes
            $classes = array_diff($classes, $declared);

            // the cache is different depending on which classes are already declared
            $name = $name.'-'.substr(md5(implode('|', $classes)), 0, 5);
        }

        $cache = $cacheDir.'/'.$name.$extension;

        // auto-reload
        $reload = false;
        if ($autoReload) {
            $metadata = $cacheDir.'/'.$name.$extension.'.meta';
            if (!is_file($metadata) || !is_file($cache)) {
                $reload = true;
            } else {
                $time = filemtime($cache);
                $meta = unserialize(file_get_contents($metadata));

                sort($meta[1]);
                sort($classes);

                if ($meta[1] != $classes) {
                    $reload = true;
                } else {
                    foreach ($meta[0] as $resource) {
                        if (!is_file($resource) || filemtime($resource) > $time) {
                            $reload = true;

                            break;
                        }
                    }
                }
            }
        }

        if (!$reload && is_file($cache)) {
            require_once $cache;

            return;
        }

        // order classes to avoid redeclaration at runtime (class declared before its parent)
        self::orderClasses($classes);

        $files = array();
        $content = '';
        foreach ($classes as $class) {
            if (!class_exists($class) && !interface_exists($class) && (!function_exists('trait_exists') || !trait_exists($class))) {
                throw new \InvalidArgumentException(sprintf('Unable to load class "%s"', $class));
            }

            $r = new \ReflectionClass($class);
            $files[] = $r->getFileName();

            $c = preg_replace(array('/^\s*<\?php/', '/\?>\s*$/'), '', file_get_contents($r->getFileName()));

            // add namespace declaration for global code
            if (!$r->inNamespace()) {
                $c = "\nnamespace\n{\n".self::stripComments($c)."\n}\n";
            } else {
                $c = self::fixNamespaceDeclarations('<?php '.$c);
                $c = preg_replace('/^\s*<\?php/', '', $c);
            }

            $content .= $c;
        }

        // cache the core classes
        if (!is_dir(dirname($cache))) {
            mkdir(dirname($cache), 0777, true);
        }
        self::writeCacheFile($cache, '<?php '.$content);

        if ($autoReload) {
            // save the resources
            self::writeCacheFile($metadata, serialize(array($files, $classes)));
        }
    }

    /**
     * Adds brackets around each namespace if it's not already the case.
     *
     * @param string $source Namespace string
     *
     * @return string Namespaces with brackets
     */
    static public function fixNamespaceDeclarations($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        $inNamespace = false;
        $tokens = token_get_all($source);

        for ($i = 0, $max = count($tokens); $i < $max; $i++) {
            $token = $tokens[$i];
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                // strip comments
                continue;
            } elseif (T_NAMESPACE === $token[0]) {
                if ($inNamespace) {
                    $output .= "}\n";
                }
                $output .= $token[1];

                // namespace name and whitespaces
                while (($t = $tokens[++$i]) && is_array($t) && in_array($t[0], array(T_WHITESPACE, T_NS_SEPARATOR, T_STRING))) {
                    $output .= $t[1];
                }
                if (is_string($t) && '{' === $t) {
                    $inNamespace = false;
                    --$i;
                } else {
                    $output = rtrim($output);
                    $output .= "\n{";
                    $inNamespace = true;
                }
            } else {
                $output .= $token[1];
            }
        }

        if ($inNamespace) {
            $output .= "}\n";
        }

        return $output;
    }

    /**
     * Writes a cache file.
     *
     * @param string $file    Filename
     * @param string $content Temporary file content
     *
     * @throws \RuntimeException when a cache file cannot be written
     */
    static private function writeCacheFile($file, $content)
    {
        $tmpFile = tempnam(dirname($file), basename($file));
        if (false !== @file_put_contents($tmpFile, $content) && @rename($tmpFile, $file)) {
            @chmod($file, 0666 & ~umask());

            return;
        }

        throw new \RuntimeException(sprintf('Failed to write cache file "%s".', $file));
    }

    /**
     * Removes comments from a PHP source string.
     *
     * We don't use the PHP php_strip_whitespace() function
     * as we want the content to be readable and well-formatted.
     *
     * @param string $source A PHP string
     *
     * @return string The PHP string with the comments removed
     */
    static private function stripComments($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (!in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= $token[1];
            }
        }

        // replace multiple new lines with a single newline
        $output = preg_replace(array('/\s+$/Sm', '/\n+/S'), "\n", $output);

        return $output;
    }

    /**
     * Orders a set of classes according to their number of parents.
     *
     * @param array $classes
     *
     * @throws \InvalidArgumentException When a class can't be loaded
     */
    static private function orderClasses(array &$classes)
    {
        foreach ($classes as $class) {
            if (isset(self::$baseClassesCountMap[$class])) {
                continue;
            }

            try {
                $reflectionClass = new \ReflectionClass($class);
            } catch (\ReflectionException $e) {
                throw new \InvalidArgumentException(sprintf('Unable to load class "%s"', $class));
            }

            // The counter is cached to avoid reflection if the same class is asked again later
            self::$baseClassesCountMap[$class] = self::countParentClasses($reflectionClass) + count($reflectionClass->getInterfaces());
        }

        asort(self::$baseClassesCountMap);

        $classes = array_intersect(array_keys(self::$baseClassesCountMap), $classes);
    }

    /**
     * Counts the number of parent classes in userland.
     *
     * @param  \ReflectionClass $class
     * @param  integer          $count If exists, the current counter
     * @return integer
     */
    static private function countParentClasses(\ReflectionClass $class, $count = 0)
    {
        if (($parent = $class->getParentClass()) && $parent->isUserDefined()) {
            $count = self::countParentClasses($parent, ++$count);
        }

        return $count;
    }
}
