<?php

namespace Symfony\Component\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Generates the classes which implement the requested lookup methods.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class GenerateLookupMethodClassesPass implements CompilerPassInterface
{
    private $generatedClasses = array();
    private $container;
    private $currentId;

    public function process(ContainerBuilder $container)
    {
        $this->container = $container;
        $this->generatedClasses = array();
        $this->cleanUpCacheDir($cacheDir = $container->getParameter('kernel.cache_dir').'/lookup_method_classes');

        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->isSynthetic() || $definition->isAbstract()) {
                continue;
            }
            if (!$methods = $definition->getLookupMethods()) {
                continue;
            }

            $this->currentId = $id;
            $this->generateClass($definition, $cacheDir);
        }
    }

    private function cleanUpCacheDir($dir)
    {
        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true)) {
                throw new \RuntimeException(sprintf('The cache directory "%s" could not be created.', $dir));
            }

            return;
        }

        if (!is_writable($dir)) {
            throw new \RuntimeException(sprintf('The cache directory "%s" is not writable.', $dir));
        }

        foreach (new \DirectoryIterator($dir) as $file) {
            if ('.' === $file->getFileName() || !is_file($file->getPathName())) {
                continue;
            }

            if (false === @unlink($file->getPathName())) {
                throw new \RuntimeException(sprintf('Could not delete auto-generated file "%s".', $file->getPathName()));
            }
        }
    }

    private function generateClass(Definition $definition, $cacheDir)
    {
        $code = <<<'EOF'
<?php

namespace Symfony\Component\DependencyInjection\LookupMethodClasses;
%s
/**
 * This class has been auto-generated by the Symfony Dependency Injection
 * Component.
 *
 * Manual changes to it will be lost.
 *
 * You can modify this class by changing your "lookup_method" configuration
 * for service "%s".
 */
class %s extends \%s
{
    private $__symfonyDependencyInjectionContainer;
%s
}
EOF;

        // other file requirement
        if ($file = $definition->getFile()) {
            $require = sprintf("\nrequire_once %s;\n", var_export($file, true));
        } else {
            $require = '';
        }

        // get class name
        $class = new \ReflectionClass($definition->getClass());
        $i = 1;
        do {
            $className = $class->getShortName();

            if ($i > 1) {
                $className .= '_'.$i;
            }

            $i += 1;
        } while (isset($this->generatedClasses[$className]));
        $this->generatedClasses[$className] = true;

        $lookupMethod = <<<'EOF'

    %s function %s()
    {
        return %s;
    }
EOF;
        $lookupMethods = '';
        foreach ($definition->getLookupMethods() as $name => $value) {
            if (!$class->hasMethod($name)) {
                throw new \RuntimeException(sprintf('The class "%s" has no method named "%s".', $class->getName(), $name));
            }
            $method = $class->getMethod($name);
            if ($method->isFinal()) {
                throw new \RuntimeException(sprintf('The method "%s::%s" is marked as final and cannot be declared as lookup-method.', $class->getName(), $name));
            }
            if ($method->isPrivate()) {
                throw new \RuntimeException(sprintf('The method "%s::%s" is marked as private and cannot be declared as lookup-method.', $class->getName(), $name));
            }
            if ($method->getParameters()) {
                throw new \RuntimeException(sprintf('The method "%s::%s" must have a no-arguments signature if you want to use it as lookup-method.', $class->getName(), $name));
            }

            $lookupMethods .= sprintf($lookupMethod,
                $method->isPublic() ? 'public' : 'protected',
                $name,
                $this->dumpValue($value)
            );
        }

        $code = sprintf($code, $require, $this->currentId, $className, $class->getName(), $lookupMethods);
        file_put_contents($cacheDir.'/'.$className.'.php', $code);
        require_once $cacheDir.'/'.$className.'.php';
        $definition->setClass('Symfony\Component\DependencyInjection\LookupMethodClasses\\'.$className);
        $definition->setFile($cacheDir.'/'.$className.'.php');
        $definition->setProperty('__symfonyDependencyInjectionContainer', new Reference('service_container'));
        $definition->setLookupMethods(array());
    }

    private function dumpValue($value)
    {
        if ($value instanceof Parameter) {
            return var_export($this->container->getParameter((string) $value), true);
        } else if ($value instanceof Reference) {
            $id = (string) $value;
            if ($this->container->hasAlias($id)) {
                $this->container->setAlias($id, (string) $this->container->getAlias());
            } else if ($this->container->hasDefinition($id)) {
                $this->container->getDefinition($id)->setPublic(true);
            }

            return '$this->__symfonyDependencyInjectionContainer->get('.var_export($id, true).', '.var_export($value->getInvalidBehavior(), true).')';
        } else if (is_array($value) || is_scalar($value) || null === $value) {
            return var_export($value, true);
        }

        throw new \RuntimeException(sprintf('Invalid value for lookup method of service "%s": %s', $this->currentId, json_encode($value)));
    }
}