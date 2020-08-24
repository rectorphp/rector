<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector;
use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Generic\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstantRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\StaticCallRename;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# source: https://book.cakephp.org/4/en/appendices/4-0-migration-guide.html

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Cake\Database\Type' => 'Cake\Database\TypeFactory',
                'Cake\Console\ConsoleErrorHandler' => 'Cake\Error\ConsoleErrorHandler',
            ],
        ]]);

    $services->set(RenameClassConstantRector::class)
        ->call('configure', [[
            RenameClassConstantRector::OLD_TO_NEW_CONSTANTS_BY_CLASS => [
                'Cake\View\View' => [
                    'NAME_ELEMENT' => 'TYPE_ELEMENT',
                    'NAME_LAYOUT' => 'TYPE_LAYOUT',
                ],
                'Cake\Mailer\Email' => [
                    'MESSAGE_HTML' => 'Cake\Mailer\Message::MESSAGE_HTML',
                    'MESSAGE_TEXT' => 'Cake\Mailer\Message::MESSAGE_TEXT',
                    'MESSAGE_BOTH' => 'Cake\Mailer\Message::MESSAGE_BOTH',
                    'EMAIL_PATTERN' => 'Cake\Mailer\Message::EMAIL_PATTERN',
                ],
            ],
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects([
                new MethodCallRename('Cake\Form\Form', 'errors', 'getErrors'),
                new MethodCallRename('Cake\Mailer\Email', 'set', 'setViewVars'),
                new MethodCallRename('Cake\ORM\EntityInterface', 'unsetProperty', 'unset'),
                new MethodCallRename('Cake\Cache\Cache', 'engine', 'pool'),
                new MethodCallRename('Cake\Http\Cookie\Cookie', 'getStringValue', 'getScalarValue'),
                new MethodCallRename('Cake\Validation\Validator', 'containsNonAlphaNumeric', 'notAlphaNumeric'),
                new MethodCallRename('Cake\Validation\Validator', 'errors', 'validate'),
            ]),
        ]]);

    $configuration = [
        new StaticCallRename('Router', 'pushRequest', 'Router', 'setRequest'),
        new StaticCallRename('Router', 'setRequestInfo', 'Router', 'setRequest'),
        new StaticCallRename('Router', 'setRequestContext', 'Router', 'setRequest'),
    ];

    $services->set(RenameStaticMethodRector::class)
        ->call('configure', [[
            RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => inline_value_objects($configuration),
        ]]);

    $services->set(RenamePropertyRector::class)
        ->call('configure', [[
            RenamePropertyRector::OLD_TO_NEW_PROPERTY_BY_TYPES => [
                'Cake\ORM\Entity' => [
                    '_properties' => '_fields',
                ],
            ],
        ]]);

    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::TYPEHINT_FOR_METHOD_BY_CLASS => [
                'Cake\Http\BaseApplication' => [
                    'bootstrap' => 'void',
                    'bootstrapCli' => 'void',
                    'middleware' => 'Cake\Http\MiddlewareQueue',
                ],
                'Cake\Console\Shell' => [
                    'initialize' => 'void',
                ],
                'Cake\Controller\Component' => [
                    'initialize' => 'void',
                ],
                'Cake\Controller\Controller' => [
                    'initialize' => 'void',
                    'render' => 'Cake\Http\Response',
                ],
                'Cake\Form\Form' => [
                    'validate' => 'bool',
                    '_buildSchema' => 'Cake\Form\Schema',
                ],
                'Cake\ORM\Behavior' => [
                    'initialize' => 'void',
                ],
                'Cake\ORM\Table' => [
                    'initialize' => 'void',
                    'updateAll' => 'int',
                    'deleteAll' => 'int',
                    'validationDefault' => 'Cake\Validation\Validator',
                    'buildRules' => 'Cake\ORM\RulesChecker',
                ],
                'Cake\View\Helper' => [
                    'initialize' => 'void',
                ],
            ],
        ]]);

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::TYPEHINT_FOR_PARAMETER_BY_METHOD_BY_CLASS => [
                'Cake\Form\Form' => [
                    'getData' => ['?string'],
                ],
                'Cake\ORM\Behavior' => [
                    'beforeFind' => ['Cake\Event\EventInterface'],
                    'buildValidator' => ['Cake\Event\EventInterface'],
                    'buildRules' => ['Cake\Event\EventInterface'],
                    'beforeRules' => ['Cake\Event\EventInterface'],
                    'afterRules' => ['Cake\Event\EventInterface'],
                    'beforeSave' => ['Cake\Event\EventInterface'],
                    'afterSave' => ['Cake\Event\EventInterface'],
                    'beforeDelete' => ['Cake\Event\EventInterface'],
                    'afterDelete' => ['Cake\Event\EventInterface'],
                ],
                'Cake\ORM\Table' => [
                    'beforeFind' => ['Cake\Event\EventInterface'],
                    'buildValidator' => ['Cake\Event\EventInterface'],
                    'buildRules' => ['Cake\ORM\RulesChecker'],
                    'beforeRules' => ['Cake\Event\EventInterface'],
                    'afterRules' => ['Cake\Event\EventInterface'],
                    'beforeSave' => ['Cake\Event\EventInterface'],
                    'afterSave' => ['Cake\Event\EventInterface'],
                    'beforeDelete' => ['Cake\Event\EventInterface'],
                    'afterDelete' => ['Cake\Event\EventInterface'],
                ],
                'Cake\Controller\Controller' => [
                    'beforeFilter' => ['Cake\Event\EventInterface'],
                    'afterFilter' => ['Cake\Event\EventInterface'],
                    'beforeRender' => ['Cake\Event\EventInterface'],
                    'beforeRedirect' => ['Cake\Event\EventInterface'],
                ],
                'Cake\Controller\Component' => [
                    'shutdown' => ['Cake\Event\EventInterface'],
                    'startup' => ['Cake\Event\EventInterface'],
                    'beforeFilter' => ['Cake\Event\EventInterface'],
                    'beforeRender' => ['Cake\Event\EventInterface'],
                    'beforeRedirect' => ['Cake\Event\EventInterface'],
                ],
            ],
        ]]);

    $services->set(RenameMethodCallBasedOnParameterRector::class)
        ->call('configure', [[
            RenameMethodCallBasedOnParameterRector::METHOD_NAMES_BY_TYPES => [
                'getParam' => [
                    'match_parameter' => 'paging',
                    'replace_with' => 'getAttribute',
                ],
                'withParam' => [
                    'match_parameter' => 'paging',
                    'replace_with' => 'withAttribute',
                ],
            ],
        ]]);

    $services->set(ModalToGetSetRector::class)
        ->call('configure', [[
            ModalToGetSetRector::METHOD_NAMES_BY_TYPES => [
                'Cake\Console\ConsoleIo' => [
                    'styles' => [
                        'set' => 'setStyle',
                        'get' => 'getStyle',
                    ],
                ],
                'Cake\Console\ConsoleOutput' => [
                    'styles' => [
                        'set' => 'setStyle',
                        'get' => 'getStyle',
                    ],
                ],
                'Cake\ORM\EntityInterface' => [
                    'isNew' => [
                        'set' => 'setNew',
                        'get' => 'isNew',
                    ],
                ],
            ],
        ]]);
};
