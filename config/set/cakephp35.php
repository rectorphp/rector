<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\ValueObject\UnprefixedMethodToGetSet;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use function Rector\SymfonyPhpConfig\inline_value_objects;
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
            RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects([
                new MethodCallRename('Cake\Database\Schema\TableSchema', 'column', 'getColumn'),
                new MethodCallRename('Cake\Database\Schema\TableSchema', 'constraint', 'getConstraint'),
                new MethodCallRename('Cake\Database\Schema\TableSchema', 'index', 'getIndex'),
            ]),
        ]]);

    $services->set(ModalToGetSetRector::class)
        ->call('configure', [[
            ModalToGetSetRector::UNPREFIXED_METHODS_TO_GET_SET => inline_value_objects([
                new UnprefixedMethodToGetSet('Cake\Cache\Cache', 'config'),
                new UnprefixedMethodToGetSet('Cake\Cache\Cache', 'registry'),
                new UnprefixedMethodToGetSet('Cake\Console\Shell', 'io'),
                new UnprefixedMethodToGetSet('Cake\Console\ConsoleIo', 'outputAs'),
                new UnprefixedMethodToGetSet('Cake\Console\ConsoleOutput', 'outputAs'),
                new UnprefixedMethodToGetSet('Cake\Database\Connection', 'logger'),
                new UnprefixedMethodToGetSet('Cake\Database\TypedResultInterface', 'returnType'),
                new UnprefixedMethodToGetSet('Cake\Database\TypedResultTrait', 'returnType'),
                new UnprefixedMethodToGetSet('Cake\Database\Log\LoggingStatement', 'logger'),
                new UnprefixedMethodToGetSet('Cake\Datasource\ModelAwareTrait', 'modelType'),
                new UnprefixedMethodToGetSet('Cake\Database\Query', 'valueBinder', 'getValueBinder', 'valueBinder'),
                new UnprefixedMethodToGetSet('Cake\Database\Schema\TableSchema', 'columnType'),
                new UnprefixedMethodToGetSet(
                    'Cake\Datasource\QueryTrait',
                    'eagerLoaded',
                    'isEagerLoaded',
                    'eagerLoaded'
                ),
                new UnprefixedMethodToGetSet('Cake\Event\EventDispatcherInterface', 'eventManager'),
                new UnprefixedMethodToGetSet('Cake\Event\EventDispatcherTrait', 'eventManager'),
                new UnprefixedMethodToGetSet('Cake\Error\Debugger', 'outputAs', 'getOutputFormat', 'setOutputFormat'),
                new UnprefixedMethodToGetSet('Cake\Http\ServerRequest', 'env', 'getEnv', 'withEnv'),
                new UnprefixedMethodToGetSet('Cake\Http\ServerRequest', 'charset', 'getCharset', 'withCharset'),
                new UnprefixedMethodToGetSet('Cake\I18n\I18n', 'locale'),
                new UnprefixedMethodToGetSet('Cake\I18n\I18n', 'translator'),
                new UnprefixedMethodToGetSet('Cake\I18n\I18n', 'defaultLocale'),
                new UnprefixedMethodToGetSet('Cake\I18n\I18n', 'defaultFormatter'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association\BelongsToMany', 'sort'),
                new UnprefixedMethodToGetSet('Cake\ORM\LocatorAwareTrait', 'tableLocator'),
                new UnprefixedMethodToGetSet('Cake\ORM\Table', 'validator'),
                new UnprefixedMethodToGetSet('Cake\Routing\RouteBuilder', 'extensions'),
                new UnprefixedMethodToGetSet('Cake\Routing\RouteBuilder', 'routeClass'),
                new UnprefixedMethodToGetSet('Cake\Routing\RouteCollection', 'extensions'),
                new UnprefixedMethodToGetSet('Cake\TestSuite\TestFixture', 'schema'),
                new UnprefixedMethodToGetSet('Cake\Utility\Security', 'salt'),
                new UnprefixedMethodToGetSet('Cake\View\View', 'template'),
                new UnprefixedMethodToGetSet('Cake\View\View', 'layout'),
                new UnprefixedMethodToGetSet('Cake\View\View', 'theme'),
                new UnprefixedMethodToGetSet('Cake\View\View', 'templatePath'),
                new UnprefixedMethodToGetSet('Cake\View\View', 'layoutPath'),
                new UnprefixedMethodToGetSet('Cake\View\View', 'autoLayout', 'isAutoLayoutEnabled', 'enableAutoLayout'),
            ]),
        ]]);
};
