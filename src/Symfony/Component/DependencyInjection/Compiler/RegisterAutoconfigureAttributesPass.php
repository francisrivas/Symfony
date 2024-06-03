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

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Reads #[Autoconfigure] attributes on definitions that are autoconfigured
 * and don't have the "container.ignore_attributes" tag.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
final class RegisterAutoconfigureAttributesPass implements CompilerPassInterface
{
    private static $registerForAutoconfiguration;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (80000 > \PHP_VERSION_ID) {
            return;
        }

        foreach ($container->getDefinitions() as $id => $definition) {
            if ($this->accept($definition) && $class = $container->getReflectionClass($definition->getClass(), false)) {
                $this->processClass($container, $class);
            }
        }
    }

    public function accept(Definition $definition): bool
    {
        return 80000 <= \PHP_VERSION_ID && $definition->isAutoconfigured() && !$definition->hasTag('container.ignore_attributes');
    }

    public function processClass(ContainerBuilder $container, \ReflectionClass $class)
    {
        $combinedAttributesArgs = null;
        foreach ($class->getAttributes(Autoconfigure::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $args = array_filter((array) $attribute->newInstance(), static function ($v): bool { return null !== $v; });

            foreach ($args['tags'] ?? [] as $i => $tag) {
                if (\is_array($tag) && [0] === array_keys($tag)) {
                    $args['tags'][$i] = [$class->name => $tag[0]];
                }
            }

            $args['tags'] = array_merge($args['tags'] ?? [], $combinedAttributesArgs['tags'] ?? []);
            $args['calls'] = array_merge($args['calls'] ?? [], $combinedAttributesArgs['calls'] ?? []);

            $combinedAttributesArgs = array_replace($combinedAttributesArgs ?? [], $args);
        }

        if (null !== $combinedAttributesArgs) {
            self::registerForAutoconfiguration($container, $class, $combinedAttributesArgs);
        }
    }

    private static function registerForAutoconfiguration(ContainerBuilder $container, \ReflectionClass $class, array $combinedAttributesArgs)
    {
        if (self::$registerForAutoconfiguration) {
            return (self::$registerForAutoconfiguration)($container, $class, $combinedAttributesArgs);
        }

        $parseDefinitions = new \ReflectionMethod(YamlFileLoader::class, 'parseDefinitions');
        $parseDefinitions->setAccessible(true);
        $yamlLoader = $parseDefinitions->getDeclaringClass()->newInstanceWithoutConstructor();

        self::$registerForAutoconfiguration = static function (ContainerBuilder $container, \ReflectionClass $class, array $combinedAttributesArgs) use ($parseDefinitions, $yamlLoader) {
            $parseDefinitions->invoke(
                $yamlLoader,
                [
                    'services' => [
                        '_instanceof' => [
                            $class->name => [$container->registerForAutoconfiguration($class->name)] + $combinedAttributesArgs,
                        ],
                    ],
                ],
                $class->getFileName(),
                false
            );
        };

        return (self::$registerForAutoconfiguration)($container, $class, $combinedAttributesArgs);
    }
}
