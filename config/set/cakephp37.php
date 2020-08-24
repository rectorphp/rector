<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\Rector\Property\ChangeSnakedFixtureNameToCamelRector;
use Rector\Generic\Rector\Assign\PropertyToMethodRector;
use Rector\Generic\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use function Rector\SymfonyPhpConfig\inline_value_objects;
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
            PropertyToMethodRector::PER_CLASS_PROPERTY_TO_METHODS => [
                'Cake\Http\Client\Response' => [
                    'body' => [
                        'get' => 'getStringBody',
                    ],
                    'json' => [
                        'get' => 'getJson',
                    ],
                    'xml' => [
                        'get' => 'getXml',
                    ],
                    'cookies' => [
                        'get' => 'getCookies',
                    ],
                    'code' => [
                        'get' => 'getStatusCode',
                    ],
                ],
                'Cake\View\View' => [
                    'request' => [
                        'get' => 'getRequest',
                        'set' => 'setRequest',
                    ],
                    'response' => [
                        'get' => 'getResponse',
                        'set' => 'setResponse',
                    ],
                    'templatePath' => [
                        'get' => 'getTemplatePath',
                        'set' => 'setTemplatePath',
                    ],
                    'template' => [
                        'get' => 'getTemplate',
                        'set' => 'setTemplate',
                    ],
                    'layout' => [
                        'get' => 'getLayout',
                        'set' => 'setLayout',
                    ],
                    'layoutPath' => [
                        'get' => 'getLayoutPath',
                        'set' => 'setLayoutPath',
                    ],
                    'autoLayout' => [
                        'get' => 'enableAutoLayout',
                        'set' => 'isAutoLayoutEnabled',
                    ],
                    'theme' => [
                        'get' => 'getTheme',
                        'set' => 'setTheme',
                    ],
                    'subDir' => [
                        'get' => 'getSubDir',
                        'set' => 'setSubDir',
                    ],
                    'plugin' => [
                        'get' => 'getPlugin',
                        'set' => 'setPlugin',
                    ],
                    'name' => [
                        'get' => 'getName',
                        'set' => 'setName',
                    ],
                    'elementCache' => [
                        'get' => 'getElementCache',
                        'set' => 'setElementCache',
                    ],
                    'helpers' => [
                        'get' => 'helpers',
                    ],
                ],
            ],
        ]]);

    $services->set(MethodCallToAnotherMethodCallWithArgumentsRector::class)
        ->call('configure', [[
            MethodCallToAnotherMethodCallWithArgumentsRector::OLD_METHODS_TO_NEW_METHODS_WITH_ARGS_BY_TYPE => [
                'Cake\Database\Query' => [
                    'join' => ['clause', ['join']],
                    'from' => ['clause', ['from']],
                ],
            ],
        ]]);

    $services->set(ModalToGetSetRector::class)
        ->call('configure', [[
            ModalToGetSetRector::METHOD_NAMES_BY_TYPES => [
                'Cake\Database\Connection' => [
                    'logQueries' => [
                        'set' => 'enableQueryLogging',
                        'get' => 'isQueryLoggingEnabled',
                    ],
                ],
                'Cake\ORM\Association' => [
                    'className' => [
                        'set' => 'setClassName',
                        'get' => 'getClassName',
                    ],
                ],
            ],
        ]]);

    $services->set(ChangeSnakedFixtureNameToCamelRector::class);
};
