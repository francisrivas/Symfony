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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Alias;

/**
 * Overwrites a service but keeps the overridden one.
 *
 * @author Christophe Coevoet <stof@notk.org>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DecoratorServicePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            if (!$decorated = $definition->getDecoratedService()) {
                continue;
            }

            list ($decorated, $renamedId) = $decorated;
            if (!$renamedId) {
                $renamedId = $id.'.parent';
            }

            // we create a new alias/service for the service we are replacing
            // to be able to reference it in the new one
            if ($container->hasAlias($decorated)) {
                $container->setAlias($renamedId, new Alias((string) $container->getAlias($decorated), false));
            } else {
                $definition = $container->getDefinition($decorated);
                $definition->setPublic(false);
                $container->setDefinition($renamedId, $definition);
            }

            $container->setAlias($decorated, $id);
        }
    }
}
