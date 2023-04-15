<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\ImportMap\Command\ExportCommand;
use Symfony\Component\ImportMap\Command\RemoveCommand;
use Symfony\Component\ImportMap\Command\RequireCommand;
use Symfony\Component\ImportMap\Command\UpdateCommand;
use Symfony\Component\ImportMap\Controller\ImportmapController;
use Symfony\Component\ImportMap\ImportMapManager;

return static function (ContainerConfigurator $container): void {
    $container->services()

        ->set('importmap.controller', ImportmapController::class)
            ->args([
                service('importmap.manager'),
                service('mime_types')->nullOnInvalid(),
            ])
            ->public()

        ->set('importmap.manager', ImportMapManager::class)
            ->args([
                abstract_arg('importmap.php path'),
                abstract_arg('vendor directory'),
                abstract_arg('public directory'),
                abstract_arg('base URL'),
                abstract_arg('provider'),
                abstract_arg('debug'),
            ])
        ->alias(ImportMapManager::class, 'importmap.manager')

        ->set('importmap.command.require', RequireCommand::class)
            ->args([service('importmap.manager')])
            ->tag('console.command')

        ->set('importmap.command.remove', RemoveCommand::class)
            ->args([service('importmap.manager')])
            ->tag('console.command')

        ->set('importmap.command.update', UpdateCommand::class)
            ->args([service('importmap.manager')])
            ->tag('console.command')

        ->set('importmap.command.export', ExportCommand::class)
            ->args([service('importmap.manager')])
            ->tag('console.command')

    ;
};
