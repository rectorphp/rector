<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Cake\Routing\Exception\RedirectException' => 'Cake\Http\Exception\RedirectException',
                'Cake\Database\Expression\Comparison' => 'Cake\Database\Expression\ComparisonExpression',
            ],
        ]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'Cake\Database\Schema\TableSchema' => [
                    'getPrimary' => 'getPrimaryKey',
                ],
                'Cake\Database\Type\DateTimeType' => [
                    'setTimezone' => 'setDatabaseTimezone',
                ],
                'Cake\Database\Expression\QueryExpression' => [
                    'or_' => 'or',
                    'and_' => 'and',
                ],
                'Cake\View\Form\ContextInterface' => [
                    'primaryKey' => 'getPrimaryKey',
                ],
                'Cake\Http\Middleware\CsrfProtectionMiddleware' => [
                    'whitelistCallback' => 'skipCheckCallback',
                ],
            ],
        ]]);

    $services->set(ModalToGetSetRector::class)
        ->call('configure', [
            ModalToGetSetRector::METHOD_NAMES_BY_TYPES => [
                'Cake\Form\Form' => [
                    'schema' => [
                        'set' => 'setSchema',
                        'get' => 'getSchema',
                    ],
                ],
            ],
        ]);
};
