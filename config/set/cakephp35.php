<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# source: https://book.cakephp.org/3.0/en/appendices/3-5-migration-guide.html
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Cake\Http\Client\CookieCollection' => 'Cake\Http\Cookie\CookieCollection',
                'Cake\Console\ShellDispatcher' => 'Cake\Console\CommandRunner',
            ],
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'Cake\Database\Schema\TableSchema' => [
                    'column' => 'getColumn',
                    'constraint' => 'getConstraint',
                    'index' => 'getIndex',
                ],
            ],
        ]]);

    $services->set(ModalToGetSetRector::class)
        ->call('configure', [[
            ModalToGetSetRector::METHOD_NAMES_BY_TYPES => [
                'Cake\Cache\Cache' => [
                    'config' => null,
                    'registry' => null,
                ],
                'Cake\Console\Shell' => [
                    'io' => null,
                ],
                'Cake\Console\ConsoleIo' => [
                    'outputAs' => null,
                ],
                'Cake\Console\ConsoleOutput' => [
                    'outputAs' => null,
                ],
                'Cake\Database\Connection' => [
                    'logger' => null,
                ],
                'Cake\Database\TypedResultInterface' => [
                    'returnType' => null,
                ],
                'Cake\Database\TypedResultTrait' => [
                    'returnType' => null,
                ],
                'Cake\Database\Log\LoggingStatement' => [
                    'logger' => null,
                ],
                'Cake\Datasource\ModelAwareTrait' => [
                    'modelType' => null,
                ],
                'Cake\Database\Query' => [
                    'valueBinder' => [
                        'get' => 'getValueBinder',
                        'set' => 'valueBinder',
                    ],
                ],
                'Cake\Database\Schema\TableSchema' => [
                    'columnType' => null,
                ],
                'Cake\Datasource\QueryTrait' => [
                    'eagerLoaded' => [
                        'get' => 'isEagerLoaded',
                        'set' => 'eagerLoaded',
                    ],
                ],
                'Cake\Event\EventDispatcherInterface' => [
                    'eventManager' => null,
                ],
                'Cake\Event\EventDispatcherTrait' => [
                    'eventManager' => null,
                ],
                'Cake\Error\Debugger' => [
                    'outputAs' => [
                        'get' => 'getOutputFormat',
                        'set' => 'setOutputFormat',
                    ],
                ],
                'Cake\Http\ServerRequest' => [
                    'env' => [
                        'get' => 'getEnv',
                        'set' => 'withEnv',
                    ],
                    'charset' => [
                        'get' => 'getCharset',
                        'set' => 'withCharset',
                    ],
                ],
                'Cake\I18n\I18n' => [
                    'locale' => null,
                    'translator' => null,
                    'defaultLocale' => null,
                    'defaultFormatter' => null,
                ],
                'Cake\ORM\Association\BelongsToMany' => [
                    'sort' => null,
                ],
                'Cake\ORM\LocatorAwareTrait' => [
                    'tableLocator' => null,
                ],
                'Cake\ORM\Table' => [
                    'validator' => null,
                ],
                'Cake\Routing\RouteBuilder' => [
                    'extensions' => null,
                    'routeClass' => null,
                ],
                'Cake\Routing\RouteCollection' => [
                    'extensions' => null,
                ],
                'Cake\TestSuite\TestFixture' => [
                    'schema' => null,
                ],
                'Cake\Utility\Security' => [
                    'salt' => null,
                ],
                'Cake\View\View' => [
                    'template' => null,
                    'layout' => null,
                    'theme' => null,
                    'templatePath' => null,
                    'layoutPath' => null,
                    'autoLayout' => [
                        'get' => 'isAutoLayoutEnabled',
                        'set' => 'enableAutoLayout',
                    ],
                ],
            ],
        ]]);
};
