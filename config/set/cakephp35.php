<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\ValueObject\ModalToGetSet;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

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
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename('Cake\Database\Schema\TableSchema', 'column', 'getColumn'),
                new MethodCallRename('Cake\Database\Schema\TableSchema', 'constraint', 'getConstraint'),
                new MethodCallRename('Cake\Database\Schema\TableSchema', 'index', 'getIndex'),
            ]),
        ]]);

    $services->set(ModalToGetSetRector::class)
        ->call('configure', [[
            ModalToGetSetRector::UNPREFIXED_METHODS_TO_GET_SET => ValueObjectInliner::inline([
                new ModalToGetSet('Cake\Cache\Cache', 'config'),
                new ModalToGetSet('Cake\Cache\Cache', 'registry'),
                new ModalToGetSet('Cake\Console\Shell', 'io'),
                new ModalToGetSet('Cake\Console\ConsoleIo', 'outputAs'),
                new ModalToGetSet('Cake\Console\ConsoleOutput', 'outputAs'),
                new ModalToGetSet('Cake\Database\Connection', 'logger'),
                new ModalToGetSet('Cake\Database\TypedResultInterface', 'returnType'),
                new ModalToGetSet('Cake\Database\TypedResultTrait', 'returnType'),
                new ModalToGetSet('Cake\Database\Log\LoggingStatement', 'logger'),
                new ModalToGetSet('Cake\Datasource\ModelAwareTrait', 'modelType'),
                new ModalToGetSet('Cake\Database\Query', 'valueBinder', 'getValueBinder', 'valueBinder'),
                new ModalToGetSet('Cake\Database\Schema\TableSchema', 'columnType'),
                new ModalToGetSet('Cake\Datasource\QueryTrait', 'eagerLoaded', 'isEagerLoaded', 'eagerLoaded'),
                new ModalToGetSet('Cake\Event\EventDispatcherInterface', 'eventManager'),
                new ModalToGetSet('Cake\Event\EventDispatcherTrait', 'eventManager'),
                new ModalToGetSet('Cake\Error\Debugger', 'outputAs', 'getOutputFormat', 'setOutputFormat'),
                new ModalToGetSet('Cake\Http\ServerRequest', 'env', 'getEnv', 'withEnv'),
                new ModalToGetSet('Cake\Http\ServerRequest', 'charset', 'getCharset', 'withCharset'),
                new ModalToGetSet('Cake\I18n\I18n', 'locale'),
                new ModalToGetSet('Cake\I18n\I18n', 'translator'),
                new ModalToGetSet('Cake\I18n\I18n', 'defaultLocale'),
                new ModalToGetSet('Cake\I18n\I18n', 'defaultFormatter'),
                new ModalToGetSet('Cake\ORM\Association\BelongsToMany', 'sort'),
                new ModalToGetSet('Cake\ORM\LocatorAwareTrait', 'tableLocator'),
                new ModalToGetSet('Cake\ORM\Table', 'validator'),
                new ModalToGetSet('Cake\Routing\RouteBuilder', 'extensions'),
                new ModalToGetSet('Cake\Routing\RouteBuilder', 'routeClass'),
                new ModalToGetSet('Cake\Routing\RouteCollection', 'extensions'),
                new ModalToGetSet('Cake\TestSuite\TestFixture', 'schema'),
                new ModalToGetSet('Cake\Utility\Security', 'salt'),
                new ModalToGetSet('Cake\View\View', 'template'),
                new ModalToGetSet('Cake\View\View', 'layout'),
                new ModalToGetSet('Cake\View\View', 'theme'),
                new ModalToGetSet('Cake\View\View', 'templatePath'),
                new ModalToGetSet('Cake\View\View', 'layoutPath'),
                new ModalToGetSet('Cake\View\View', 'autoLayout', 'isAutoLayoutEnabled', 'enableAutoLayout'),
            ]),
        ]]);
};
