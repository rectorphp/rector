<?php

declare(strict_types=1);

use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

# https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.1.md

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy' => 'Symfony\Component\EventDispatcher\EventDispatcherInterface'
            ]
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename('Symfony\Component\Config\Definition\BaseNode', 'getDeprecationMessage','getDeprecation'),
                new MethodCallRename('Symfony\Component\DependencyInjection\Definition', 'getDeprecationMessage','getDeprecation'),
                new MethodCallRename('Symfony\Component\DependencyInjection\Alias', 'getDeprecationMessage','getDeprecation'),
            ])
        ]]);

    $services->set(RenameFunctionRector::class)
        ->call('configure', [[
            RenameFunctionRector::OLD_FUNCTION_TO_NEW_FUNCTION => [
                'Symfony\Component\DependencyInjection\Loader\Configuraton\inline' => 'Symfony\Component\DependencyInjection\Loader\Configuraton\inline_service',
                'Symfony\Component\DependencyInjection\Loader\Configuraton\ref' => 'Symfony\Component\DependencyInjection\Loader\Configuraton\service',
            ]
        ]]);
};
