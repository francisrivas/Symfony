<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Scheduler\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Scheduler\TraceableScheduler;
use Symfony\Component\Scheduler\Worker\TraceableWorker;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SchedulerPass implements CompilerPassInterface
{
    private $schedulerTag;
    private $workerTag;

    public function __construct(string $schedulerTag = 'scheduler.hub', string $workerTag = 'scheduler.worker')
    {
        $this->schedulerTag = $schedulerTag;
        $this->workerTag = $workerTag;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->registerSchedulerToCollector($container);
        $this->registerWorkerToCollector($container);
        $this->triggerCronGeneration($container);
    }

    private function registerSchedulerToCollector(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds($this->schedulerTag) as $schedulerId => $tags) {
            $container->setDefinition(
                $tracedId = 'debug.scheduler.hub.'.$schedulerId,
                (new Definition(TraceableScheduler::class, [new Reference($tracedId.'.inner')]))->setDecoratedService($schedulerId)
            );
            $container->getDefinition('scheduler.data_collector')->addMethodCall('registerScheduler', [$schedulerId, new Reference($tracedId)]);
        }
    }

    private function registerWorkerToCollector(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds($this->workerTag) as $workerId => $tags) {
            $container->setDefinition(
                $tracedId = 'debug.scheduler.worker.'.$workerId,
                (new Definition(TraceableWorker::class, [new Reference($tracedId.'.inner')]))->setDecoratedService($workerId)
            );
            $container->getDefinition('scheduler.data_collector')->addMethodCall('registerWorker', [$workerId, new Reference($tracedId)]);
        }
    }

    private function triggerCronGeneration(ContainerBuilder $container): void
    {
        $schedulers = [];

        foreach ($container->findTaggedServiceIds($this->schedulerTag) as $schedulerId => $tags) {
            $schedulers[$schedulerId] = $tags[0];
        }

        foreach ($schedulers as $scheduler) {
            if (!\array_key_exists('alias', $scheduler) && !\array_key_exists('transport', $scheduler)) {
                continue;
            }

            $container->getDefinition('scheduler.cron.factory')->addMethodCall('create', [
                $scheduler['alias'],
                new Reference($scheduler['transport']),
                ['path' => $container->getParameter('kernel.project_dir')]
            ]);
        }
    }
}
