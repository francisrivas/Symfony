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

use Symfony\Component\DependencyInjection\Argument\ArgumentInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Inline service definitions where this is possible.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class InlineServiceDefinitionsPass extends AbstractRecursivePass implements RepeatablePassInterface
{
    private $analyzingPass;
    private $repeatedPass;
    private $cloningIds = array();
    private $connectedIds = array();
    private $notInlinedIds = array();
    private $inlinedIds = array();
    private $graph;

    public function __construct(AnalyzeServiceReferencesPass $analyzingPass = null)
    {
        $this->analyzingPass = $analyzingPass;
    }

    /**
     * {@inheritdoc}
     */
    public function setRepeatedPass(RepeatedPass $repeatedPass)
    {
        @trigger_error(sprintf('The "%s()" method is deprecated since Symfony 4.2.', __METHOD__), E_USER_DEPRECATED);
        $this->repeatedPass = $repeatedPass;
    }

    public function process(ContainerBuilder $container)
    {
        $this->container = $container;
        if ($this->analyzingPass) {
            $analyzedContainer = new ContainerBuilder();
            $analyzedContainer->setAliases($container->getAliases());
            $analyzedContainer->setDefinitions($container->getDefinitions());
            foreach ($container->getExpressionLanguageProviders() as $provider) {
                $analyzedContainer->addExpressionLanguageProvider($provider);
            }
        } else {
            $analyzedContainer = $container;
        }
        try {
            $this->connectedIds = $this->notInlinedIds = $container->getDefinitions();
            do {
                if ($this->analyzingPass) {
                    $analyzedContainer->setDefinitions(array_intersect_key($analyzedContainer->getDefinitions(), $this->connectedIds));
                    $this->analyzingPass->process($analyzedContainer);
                }
                $this->graph = $analyzedContainer->getCompiler()->getServiceReferenceGraph();
                $notInlinedIds = $this->notInlinedIds;
                $this->connectedIds = $this->notInlinedIds = $this->inlinedIds = array();

                foreach ($analyzedContainer->getDefinitions() as $id => $definition) {
                    if (!$this->graph->hasNode($id)) {
                        continue;
                    }
                    foreach ($this->graph->getNode($id)->getOutEdges() as $edge) {
                        if (isset($notInlinedIds[$edge->getSourceNode()->getId()])) {
                            $this->currentId = $id;
                            $this->processValue($definition, true);
                            break;
                        }
                    }
                }

                foreach ($this->inlinedIds as $id => $isPublic) {
                    if (!$isPublic) {
                        $container->removeDefinition($id);
                        $analyzedContainer->removeDefinition($id);
                    }
                }
            } while ($this->inlinedIds && $this->analyzingPass);

            if ($this->inlinedIds && $this->repeatedPass) {
                $this->repeatedPass->setRepeat();
            }
        } finally {
            $this->container = null;
            $this->connectedIds = $this->notInlinedIds = $this->inlinedIds = array();
            $this->graph = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        if ($value instanceof ArgumentInterface) {
            // Reference found in ArgumentInterface::getValues() are not inlineable
            return $value;
        }

        if ($value instanceof Definition && $this->cloningIds) {
            if ($value->isShared()) {
                return $value;
            }
            $value = clone $value;
        }

        if (!$value instanceof Reference) {
            return parent::processValue($value, $isRoot);
        } elseif (!$this->container->hasDefinition($id = (string) $value)) {
            return $value;
        }

        $definition = $this->container->getDefinition($id);

        if (!$this->isInlineableDefinition($id, $definition)) {
            return $value;
        }

        $this->container->log($this, sprintf('Inlined service "%s" to "%s".', $id, $this->currentId));
        $this->inlinedIds[$id] = $definition->isPublic();
        $this->notInlinedIds[$this->currentId] = true;

        if ($definition->isShared()) {
            return $definition;
        }

        if (isset($this->cloningIds[$id])) {
            $ids = array_keys($this->cloningIds);
            $ids[] = $id;

            throw new ServiceCircularReferenceException($id, \array_slice($ids, array_search($id, $ids)));
        }

        $this->cloningIds[$id] = true;
        try {
            return $this->processValue($definition);
        } finally {
            unset($this->cloningIds[$id]);
        }
    }

    /**
     * Checks if the definition is inlineable.
     *
     * @return bool If the definition is inlineable
     */
    private function isInlineableDefinition($id, Definition $definition)
    {
        if ($definition->getErrors() || $definition->isDeprecated() || $definition->isLazy() || $definition->isSynthetic()) {
            return false;
        }

        if (!$definition->isShared()) {
            foreach ($this->graph->getNode($id)->getInEdges() as $edge) {
                $srcId = $edge->getSourceNode()->getId();
                $this->connectedIds[$srcId] = true;
                if ($edge->isWeak()) {
                    return false;
                }
            }

            return true;
        }

        if ($definition->isPublic()) {
            return false;
        }

        if (!$this->graph->hasNode($id)) {
            return true;
        }

        if ($this->currentId == $id) {
            return false;
        }
        $this->connectedIds[$id] = true;

        $srcIds = array();
        $srcCount = 0;
        foreach ($this->graph->getNode($id)->getInEdges() as $edge) {
            $srcId = $edge->getSourceNode()->getId();
            $this->connectedIds[$srcId] = true;
            if ($edge->isWeak()) {
                return false;
            }
            $srcIds[$srcId] = true;
            ++$srcCount;
        }

        if (1 !== \count($srcIds)) {
            $this->notInlinedIds[$id] = true;

            return false;
        }

        if ($srcCount > 1 && \is_array($factory = $definition->getFactory()) && ($factory[0] instanceof Reference || $factory[0] instanceof Definition)) {
            return false;
        }

        return $this->container->getDefinition($srcId)->isShared();
    }
}
