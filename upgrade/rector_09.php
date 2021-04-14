<?php

declare(strict_types=1);

use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Fixture\RenameMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassConstFetchRector::class)
        ->call('configure', [[
            RenameClassConstFetchRector::CLASS_CONSTANT_RENAME => ValueObjectInliner::inline([
                new RenameClassAndConstFetch(
                    'Rector\Core\Configuration\Option',
                    'EXCLUDE_RECTORS',
                    'Rector\Core\Configuration\Option',
                    'SKIP',
                )
            ])
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename('Rector\Core\Rector\AbstractRector', 'getDefinition', 'getRuleDefinition')
            ])
        ]]);

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Rector\Core\RectorDefinition\CodeSample' => 'Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample',
                'Rector\Core\RectorDefinition\RectorDefinition' => 'Symplify\RuleDocGenerator\ValueObject\RuleDefinition',
            ]
        ]]);
};
