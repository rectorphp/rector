<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\Rector\Property\ChangeSnakedFixtureNameToPascalRector;
use Rector\CakePHP\ValueObject\UnprefixedMethodToGetSet;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\Assign\PropertyToMethodRector;
use Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;
use Rector\Transform\ValueObject\MethodCallRenameWithAddedArguments;
use Rector\Transform\ValueObject\PropertyToMethodCall;
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
                new PropertyToMethodCall('Cake\Http\Client\Response', 'body', 'getStringBody'),
                new PropertyToMethodCall('Cake\Http\Client\Response', 'json', 'getJson'),
                new PropertyToMethodCall('Cake\Http\Client\Response', 'xml', 'getXml'),
                new PropertyToMethodCall('Cake\Http\Client\Response', 'cookies', 'getCookies'),
                new PropertyToMethodCall('Cake\Http\Client\Response', 'code', 'getStatusCode'),

                new PropertyToMethodCall('Cake\View\View', 'request', 'getRequest', 'setRequest'),
                new PropertyToMethodCall('Cake\View\View', 'response', 'getResponse', 'setResponse'),
                new PropertyToMethodCall('Cake\View\View', 'templatePath', 'getTemplatePath', 'setTemplatePath'),
                new PropertyToMethodCall('Cake\View\View', 'template', 'getTemplate', 'setTemplate'),
                new PropertyToMethodCall('Cake\View\View', 'layout', 'getLayout', 'setLayout'),
                new PropertyToMethodCall('Cake\View\View', 'layoutPath', 'getLayoutPath', 'setLayoutPath'),
                new PropertyToMethodCall('Cake\View\View', 'autoLayout', 'isAutoLayoutEnabled', 'enableAutoLayout'),
                new PropertyToMethodCall('Cake\View\View', 'theme', 'getTheme', 'setTheme'),
                new PropertyToMethodCall('Cake\View\View', 'subDir', 'getSubDir', 'setSubDir'),
                new PropertyToMethodCall('Cake\View\View', 'plugin', 'getPlugin', 'setPlugin'),
                new PropertyToMethodCall('Cake\View\View', 'name', 'getName', 'setName'),
                new PropertyToMethodCall('Cake\View\View', 'elementCache', 'getElementCache', 'setElementCache'),
                new PropertyToMethodCall('Cake\View\View', 'helpers', 'helpers'),
            ]),
        ]]);

    $services->set(MethodCallToAnotherMethodCallWithArgumentsRector::class)
        ->call('configure', [[
            MethodCallToAnotherMethodCallWithArgumentsRector::METHOD_CALL_RENAMES_WITH_ADDED_ARGUMENTS => inline_value_objects(
                [
                    new MethodCallRenameWithAddedArguments('Cake\Database\Query', 'join', 'clause', ['join']),
                    new MethodCallRenameWithAddedArguments('Cake\Database\Query', 'from', 'clause', ['from']),
                ]
            ),
        ]]);

    $services->set(ModalToGetSetRector::class)
        ->call('configure', [[
            ModalToGetSetRector::UNPREFIXED_METHODS_TO_GET_SET => inline_value_objects([
                new UnprefixedMethodToGetSet(
                    'Cake\Database\Connection',
                    'logQueries',
                    'isQueryLoggingEnabled',
                    'enableQueryLogging'
                ),
                new UnprefixedMethodToGetSet('Cake\ORM\Association', 'className', 'getClassName', 'setClassName'),
            ]),
        ]]);

    $services->set(ChangeSnakedFixtureNameToPascalRector::class);
};
