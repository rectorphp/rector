<?php

declare(strict_types=1);

use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\VoidType;
use Rector\Composer\Rector\ChangePackageVersionComposerRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Rector\Core\Configuration\Option;
use Rector\Removing\Rector\Class_\RemoveInterfacesRector;
use Rector\Removing\Rector\Class_\RemoveParentRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\Class_\AddInterfaceByParentRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $services = $containerConfigurator->services();

    $services->set(ChangePackageVersionComposerRector::class)
        ->call('configure', [[
            ChangePackageVersionComposerRector::PACKAGES_AND_VERSIONS => ValueObjectInliner::inline([
                new PackageAndVersion('league/event', '^3.0'),
            ]),
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename('League\Event\EventInterface', 'getName', 'eventName'),
                new MethodCallRename('League\Event\EmitterInterface', 'emit', 'dispatch'),
                new MethodCallRename('League\Event\EmitterInterface', 'addListener', 'subscribeTo'),
                new MethodCallRename('League\Event\EmitterInterface', 'addOneTimeListener', 'subscribeOneTo'),
                new MethodCallRename('League\Event\EmitterInterface', 'useListenerProvider', 'subscribeListenersFrom'),
                new MethodCallRename('League\Event\ListenerInterface', 'handle', '__invoke'),
            ]),
        ]]);

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => ValueObjectInliner::inline([
                new AddParamTypeDeclaration(
                    'League\Event\ListenerInterface',
                    '__invoke',
                    0,
                    new ObjectWithoutClassType()
                ),
            ]),
        ]]);

    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => ValueObjectInliner::inline([
                new AddReturnTypeDeclaration('League\Event\EventInterface', 'eventName', new StringType()),
                new AddReturnTypeDeclaration('League\Event\ListenerInterface', '__invoke', new VoidType()),
            ]),
        ]]);

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'League\Event\Emitter' => 'League\Event\EventDispatcher',
                'League\Event\ListenerInterface' => 'League\Event\Listener',
                'League\Event\GeneratorInterface' => 'League\Event\EventGenerator',
                'League\Event\ListenerProviderInterface' => 'League\Event\ListenerSubscriber',
                'League\Event\ListenerAcceptorInterface' => 'League\Event\ListenerRegistry',
            ],
        ]]);

    $services->set(AddInterfaceByParentRector::class)
        ->call('configure', [[
            AddInterfaceByParentRector::INTERFACE_BY_PARENT => [
                'League\Event\AbstractEvent' => 'League\Event\HasEventName',
                'League\Event\AbstractListener' => 'League\Event\Listener',
            ],
        ]]);

    $services->set(RemoveInterfacesRector::class)
        ->call('configure', [[
            RemoveInterfacesRector::INTERFACES_TO_REMOVE => [
                'League\Event\EventInterface',
            ]
        ]]);

    $services->set(RemoveParentRector::class)
        ->call('configure', [[
            RemoveParentRector::PARENT_TYPES_TO_REMOVE => [
                'League\Event\AbstractEvent',
                'League\Event\Event',
                'League\Event\AbstractListener',
            ]
        ]]);
};
