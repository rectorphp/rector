<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\Rector\Property\ChangeSnakedFixtureNameToPascalRector;
use Rector\CakePHP\ValueObject\ModalToGetSet;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\Assign\PropertyToMethodRector;
use Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;
use Rector\Transform\ValueObject\MethodCallToAnotherMethodCallWithArguments;
use Rector\Transform\ValueObject\PropertyToMethod;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# source: https://book.cakephp.org/3.0/en/appendices/3-7-migration-guide.html
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects([
                new MethodCallRename('Cake\Form\Form', 'errors', 'getErrors'),
                new MethodCallRename('Cake\Validation\Validation', 'cc', 'creditCard'),
                new MethodCallRename('Cake\Filesystem\Folder', 'normalizePath', 'correctSlashFor'),
                new MethodCallRename('Cake\Http\Client\Response', 'body', 'getStringBody'),
                new MethodCallRename('Cake\Core\Plugin', 'unload', 'clear'),
            ]),
        ]]);

    $services->set(PropertyToMethodRector::class)
        ->call('configure', [[
            PropertyToMethodRector::PROPERTIES_TO_METHOD_CALLS => inline_value_objects([
                new PropertyToMethod('Cake\Http\Client\Response', 'body', 'getStringBody'),
                new PropertyToMethod('Cake\Http\Client\Response', 'json', 'getJson'),
                new PropertyToMethod('Cake\Http\Client\Response', 'xml', 'getXml'),
                new PropertyToMethod('Cake\Http\Client\Response', 'cookies', 'getCookies'),
                new PropertyToMethod('Cake\Http\Client\Response', 'code', 'getStatusCode'),

                new PropertyToMethod('Cake\View\View', 'request', 'getRequest', 'setRequest'),
                new PropertyToMethod('Cake\View\View', 'response', 'getResponse', 'setResponse'),
                new PropertyToMethod('Cake\View\View', 'templatePath', 'getTemplatePath', 'setTemplatePath'),
                new PropertyToMethod('Cake\View\View', 'template', 'getTemplate', 'setTemplate'),
                new PropertyToMethod('Cake\View\View', 'layout', 'getLayout', 'setLayout'),
                new PropertyToMethod('Cake\View\View', 'layoutPath', 'getLayoutPath', 'setLayoutPath'),
                new PropertyToMethod('Cake\View\View', 'autoLayout', 'isAutoLayoutEnabled', 'enableAutoLayout'),
                new PropertyToMethod('Cake\View\View', 'theme', 'getTheme', 'setTheme'),
                new PropertyToMethod('Cake\View\View', 'subDir', 'getSubDir', 'setSubDir'),
                new PropertyToMethod('Cake\View\View', 'plugin', 'getPlugin', 'setPlugin'),
                new PropertyToMethod('Cake\View\View', 'name', 'getName', 'setName'),
                new PropertyToMethod('Cake\View\View', 'elementCache', 'getElementCache', 'setElementCache'),
                new PropertyToMethod('Cake\View\View', 'helpers', 'helpers'),
            ]),
        ]]);

    $services->set(MethodCallToAnotherMethodCallWithArgumentsRector::class)
        ->call('configure', [[
            MethodCallToAnotherMethodCallWithArgumentsRector::METHOD_CALL_RENAMES_WITH_ADDED_ARGUMENTS => inline_value_objects(
                [
                    new MethodCallToAnotherMethodCallWithArguments('Cake\Database\Query', 'join', 'clause', ['join']),
                    new MethodCallToAnotherMethodCallWithArguments('Cake\Database\Query', 'from', 'clause', ['from']),
                ]
            ),
        ]]);

    $services->set(ModalToGetSetRector::class)
        ->call('configure', [[
            ModalToGetSetRector::UNPREFIXED_METHODS_TO_GET_SET => inline_value_objects([
                new ModalToGetSet(
                    'Cake\Database\Connection',
                    'logQueries',
                    'isQueryLoggingEnabled',
                    'enableQueryLogging'
                ),
                new ModalToGetSet('Cake\ORM\Association', 'className', 'getClassName', 'setClassName'),
            ]),
        ]]);

    $services->set(ChangeSnakedFixtureNameToPascalRector::class);
};
