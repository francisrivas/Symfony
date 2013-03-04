<?php

namespace Symfony\Bundle\TwigBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 */
class FormExtensionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->has('form.extension')) {
            return;
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config'));
        $loader->load('form.xml');

        $reflClass = new \ReflectionClass('Symfony\Bridge\Twig\Extension\FormExtension');
        $container->getDefinition('twig.loader')->addMethodCall('addPath', array(dirname(dirname($reflClass->getFileName())).'/Resources/views/Form'));
    }
}
