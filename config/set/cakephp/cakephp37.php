<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\Rector\Name\ChangeSnakedFixtureNameToCamelRector;
use Rector\Core\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;
use Rector\Core\Rector\Property\PropertyToMethodRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->arg('$oldToNewMethodsByClass', [
            'Cake\Form\Form' => [
                # source: https://book.cakephp.org/3.0/en/appendices/3-7-migration-guide.html
                'errors' => 'getErrors',
            ],
            'Cake\Validation\Validation' => [
                'cc' => 'creditCard',
            ],
            'Cake\Filesystem\Folder' => [
                'normalizePath' => 'correctSlashFor',
            ],
            'Cake\Http\Client\Response' => [
                'body' => 'getStringBody',
            ],
            'Cake\Core\Plugin' => [
                'unload' => 'clear',
            ],
        ]);

    $services->set(PropertyToMethodRector::class)
        ->arg('$perClassPropertyToMethods', [
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
        ]);

    $services->set(MethodCallToAnotherMethodCallWithArgumentsRector::class)
        ->arg('$oldMethodsToNewMethodsWithArgsByType', [
            'Cake\Database\Query' => [
                'join' => ['clause', ['join']],
                'from' => ['clause', ['from']],
            ],
        ]);

    $services->set(ModalToGetSetRector::class)
        ->arg('$methodNamesByTypes', [
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
        ]);

    $services->set(ChangeSnakedFixtureNameToCamelRector::class);
};
