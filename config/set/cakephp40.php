<?php

declare(strict_types=1);

use PHPStan\Type\BooleanType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector;
use Rector\CakePHP\ValueObject\ModalToGetSet;
use Rector\CakePHP\ValueObject\RenameMethodCallBasedOnParameter;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Renaming\ValueObject\RenameProperty;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

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

    $services->set(RenameClassConstFetchRector::class)
        ->call('configure', [[
            RenameClassConstFetchRector::CLASS_CONSTANT_RENAME => ValueObjectInliner::inline([
                new RenameClassConstFetch('Cake\View\View', 'NAME_ELEMENT', 'TYPE_ELEMENT'),
                new RenameClassConstFetch('Cake\View\View', 'NAME_LAYOUT', 'TYPE_LAYOUT'),
                new RenameClassAndConstFetch(
                    'Cake\Mailer\Email',
                    'MESSAGE_HTML',
                    'Cake\Mailer\Message',
                    'MESSAGE_HTML'
                ),
                new RenameClassAndConstFetch(
                    'Cake\Mailer\Email',
                    'MESSAGE_TEXT',
                    'Cake\Mailer\Message',
                    'MESSAGE_TEXT'
                ),
                new RenameClassAndConstFetch(
                    'Cake\Mailer\Email',
                    'MESSAGE_BOTH',
                    'Cake\Mailer\Message',
                    'MESSAGE_BOTH'
                ),
                new RenameClassAndConstFetch(
                    'Cake\Mailer\Email',
                    'EMAIL_PATTERN',
                    'Cake\Mailer\Message',
                    'EMAIL_PATTERN'
                ),
            ]),
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename('Cake\Form\Form', 'errors', 'getErrors'),
                new MethodCallRename('Cake\Mailer\Email', 'set', 'setViewVars'),
                new MethodCallRename('Cake\ORM\EntityInterface', 'unsetProperty', 'unset'),
                new MethodCallRename('Cake\Cache\Cache', 'engine', 'pool'),
                new MethodCallRename('Cake\Http\Cookie\Cookie', 'getStringValue', 'getScalarValue'),
                new MethodCallRename('Cake\Validation\Validator', 'containsNonAlphaNumeric', 'notAlphaNumeric'),
                new MethodCallRename('Cake\Validation\Validator', 'errors', 'validate'),
            ]),
        ]]);

    $services->set(RenameStaticMethodRector::class)
        ->call('configure', [[
            RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => ValueObjectInliner::inline([
                new RenameStaticMethod('Router', 'pushRequest', 'Router', 'setRequest'),
                new RenameStaticMethod('Router', 'setRequestInfo', 'Router', 'setRequest'),
                new RenameStaticMethod('Router', 'setRequestContext', 'Router', 'setRequest'),
            ]),
        ]]);

    $services->set(RenamePropertyRector::class)
        ->call('configure', [[
            RenamePropertyRector::RENAMED_PROPERTIES => ValueObjectInliner::inline([
                new RenameProperty('Cake\ORM\Entity', '_properties', '_fields'),
            ]),
        ]]);

    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => ValueObjectInliner::inline([
                new AddReturnTypeDeclaration('Cake\Http\BaseApplication', 'bootstrap', new VoidType()),
                new AddReturnTypeDeclaration('Cake\Http\BaseApplication', 'bootstrapCli', new VoidType()),
                new AddReturnTypeDeclaration('Cake\Http\BaseApplication', 'middleware', new ObjectType(
                    'Cake\Http\MiddlewareQueue'
                )),
                new AddReturnTypeDeclaration('Cake\Console\Shell', 'initialize', new VoidType()),
                new AddReturnTypeDeclaration('Cake\Controller\Component', 'initialize', new VoidType()),
                new AddReturnTypeDeclaration('Cake\Controller\Controller', 'initialize', new VoidType()),
                new AddReturnTypeDeclaration('Cake\Controller\Controller', 'render', new ObjectType(
                    'Cake\Http\Response'
                )),
                new AddReturnTypeDeclaration('Cake\Form\Form', 'validate', new BooleanType()),
                new AddReturnTypeDeclaration('Cake\Form\Form', '_buildSchema', new ObjectType('Cake\Form\Schema')),
                new AddReturnTypeDeclaration('Cake\ORM\Behavior', 'initialize', new VoidType()),
                new AddReturnTypeDeclaration('Cake\ORM\Table', 'initialize', new VoidType()),
                new AddReturnTypeDeclaration('Cake\ORM\Table', 'updateAll', new IntegerType()),
                new AddReturnTypeDeclaration('Cake\ORM\Table', 'deleteAll', new IntegerType()),
                new AddReturnTypeDeclaration('Cake\ORM\Table', 'validationDefault', new ObjectType(
                    'Cake\Validation\Validator'
                )),
                new AddReturnTypeDeclaration('Cake\ORM\Table', 'buildRules', new ObjectType('Cake\ORM\RulesChecker')),
                new AddReturnTypeDeclaration('Cake\View\Helper', 'initialize', new VoidType()), ]),
        ]]);

    $eventInterfaceObjectType = new ObjectType('Cake\Event\EventInterface');

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => ValueObjectInliner::inline([
                new AddParamTypeDeclaration(
                    'Cake\Form\Form',
                    'getData',
                    0,
                    new UnionType([new StringType(), new NullType()])
                ),
                new AddParamTypeDeclaration('Cake\ORM\Behavior', 'beforeFind', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration('Cake\ORM\Behavior', 'buildValidator', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration('Cake\ORM\Behavior', 'buildRules', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration('Cake\ORM\Behavior', 'beforeRules', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration('Cake\ORM\Behavior', 'afterRules', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration('Cake\ORM\Behavior', 'beforeSave', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration('Cake\ORM\Behavior', 'afterSave', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration('Cake\ORM\Behavior', 'beforeDelete', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration('Cake\ORM\Behavior', 'afterDelete', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration('Cake\ORM\Table', 'beforeFind', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration('Cake\ORM\Table', 'buildValidator', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration('Cake\ORM\Table', 'buildRules', 0, new ObjectType('Cake\ORM\RulesChecker')),
                new AddParamTypeDeclaration('Cake\ORM\Table', 'beforeRules', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration('Cake\ORM\Table', 'afterRules', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration('Cake\ORM\Table', 'beforeSave', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration('Cake\ORM\Table', 'afterSave', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration('Cake\ORM\Table', 'beforeDelete', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration('Cake\ORM\Table', 'afterDelete', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration(
                    'Cake\Controller\Controller',
                    'beforeFilter',
                    0,
                    $eventInterfaceObjectType
                ),
                new AddParamTypeDeclaration(
                    'Cake\Controller\Controller',
                    'afterFilter',
                    0,
                    $eventInterfaceObjectType
                ),
                new AddParamTypeDeclaration(
                    'Cake\Controller\Controller',
                    'beforeRender',
                    0,
                    $eventInterfaceObjectType
                ),
                new AddParamTypeDeclaration(
                    'Cake\Controller\Controller',
                    'beforeRedirect',
                    0,
                    $eventInterfaceObjectType
                ),
                new AddParamTypeDeclaration('Cake\Controller\Component', 'shutdown', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration('Cake\Controller\Component', 'startup', 0, $eventInterfaceObjectType),
                new AddParamTypeDeclaration(
                    'Cake\Controller\Component',
                    'beforeFilter',
                    0,
                    $eventInterfaceObjectType
                ),
                new AddParamTypeDeclaration(
                    'Cake\Controller\Component',
                    'beforeRender',
                    0,
                    $eventInterfaceObjectType
                ),
                new AddParamTypeDeclaration(
                    'Cake\Controller\Component',
                    'beforeRedirect',
                    0,
                    $eventInterfaceObjectType
                ),
            ]),
        ]]);

    $services->set(RenameMethodCallBasedOnParameterRector::class)
        ->call('configure', [[
            RenameMethodCallBasedOnParameterRector::CALLS_WITH_PARAM_RENAMES => ValueObjectInliner::inline([
                new RenameMethodCallBasedOnParameter('Cake\Http\ServerRequest', 'getParam', 'paging', 'getAttribute'),
                new RenameMethodCallBasedOnParameter('Cake\Http\ServerRequest', 'withParam', 'paging', 'withAttribute'),
            ]),
        ]]);

    $services->set(ModalToGetSetRector::class)
        ->call('configure', [[
            ModalToGetSetRector::UNPREFIXED_METHODS_TO_GET_SET => ValueObjectInliner::inline([
                new ModalToGetSet('Cake\Console\ConsoleIo', 'styles', 'setStyle', 'getStyle'),
                new ModalToGetSet('Cake\Console\ConsoleOutput', 'styles', 'setStyle', 'getStyle'),
                new ModalToGetSet('Cake\ORM\EntityInterface', 'isNew', 'setNew', 'isNew'),
            ]),
        ]]);
};
