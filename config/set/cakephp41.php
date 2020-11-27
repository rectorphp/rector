<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\ValueObject\ModalToGetSet;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Cake\Routing\Exception\RedirectException' => 'Cake\Http\Exception\RedirectException',
                'Cake\Database\Expression\Comparison' => 'Cake\Database\Expression\ComparisonExpression',
            ],
        ],
        ]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename('Cake\Database\Schema\TableSchema', 'getPrimary', 'getPrimaryKey'),
                new MethodCallRename('Cake\Database\Type\DateTimeType', 'setTimezone', 'setDatabaseTimezone'),
                new MethodCallRename('Cake\Database\Expression\QueryExpression', 'or_', 'or'),
                new MethodCallRename('Cake\Database\Expression\QueryExpression', 'and_', 'and'),
                new MethodCallRename('Cake\View\Form\ContextInterface', 'primaryKey', 'getPrimaryKey'),
                new MethodCallRename(
                    'Cake\Http\Middleware\CsrfProtectionMiddleware',
                    'whitelistCallback',
                    'skipCheckCallback'
                ),
            ]),
        ]]);

    $services->set(ModalToGetSetRector::class)
        ->call('configure', [[
            ModalToGetSetRector::UNPREFIXED_METHODS_TO_GET_SET => ValueObjectInliner::inline([
                new ModalToGetSet('Cake\Form\Form', 'schema'),
            ]),
        ]]);
};
