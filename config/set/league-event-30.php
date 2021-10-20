<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\VoidType;
use Rector\Composer\Rector\ChangePackageVersionComposerRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Rector\Core\Configuration\Option;
use Rector\LeagueEvent\Rector\MethodCall\DispatchStringToObjectRector;
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
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES, \true);
    $services = $containerConfigurator->services();
    $services->set(\Rector\Composer\Rector\ChangePackageVersionComposerRector::class)->call('configure', [[\Rector\Composer\Rector\ChangePackageVersionComposerRector::PACKAGES_AND_VERSIONS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Composer\ValueObject\PackageAndVersion('league/event', '^3.0')])]]);
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->call('configure', [[\Rector\Renaming\Rector\MethodCall\RenameMethodRector::METHOD_CALL_RENAMES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Renaming\ValueObject\MethodCallRename('League\\Event\\EventInterface', 'getName', 'eventName'), new \Rector\Renaming\ValueObject\MethodCallRename('League\\Event\\EmitterInterface', 'emit', 'dispatch'), new \Rector\Renaming\ValueObject\MethodCallRename('League\\Event\\EmitterInterface', 'addListener', 'subscribeTo'), new \Rector\Renaming\ValueObject\MethodCallRename('League\\Event\\EmitterInterface', 'addOneTimeListener', 'subscribeOneTo'), new \Rector\Renaming\ValueObject\MethodCallRename('League\\Event\\EmitterInterface', 'useListenerProvider', 'subscribeListenersFrom'), new \Rector\Renaming\ValueObject\MethodCallRename('League\\Event\\ListenerInterface', 'handle', '__invoke')])]]);
    $services->set(\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector::class)->call('configure', [[\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('League\\Event\\ListenerInterface', '__invoke', 0, new \PHPStan\Type\ObjectWithoutClassType())])]]);
    $services->set(\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector::class)->call('configure', [[\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('League\\Event\\EventInterface', 'eventName', new \PHPStan\Type\StringType()), new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('League\\Event\\ListenerInterface', '__invoke', new \PHPStan\Type\VoidType())])]]);
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->call('configure', [[\Rector\Renaming\Rector\Name\RenameClassRector::OLD_TO_NEW_CLASSES => ['League\\Event\\Emitter' => 'League\\Event\\EventDispatcher', 'League\\Event\\ListenerInterface' => 'League\\Event\\Listener', 'League\\Event\\GeneratorInterface' => 'League\\Event\\EventGenerator', 'League\\Event\\ListenerProviderInterface' => 'League\\Event\\ListenerSubscriber', 'League\\Event\\ListenerAcceptorInterface' => 'League\\Event\\ListenerRegistry']]]);
    $services->set(\Rector\Transform\Rector\Class_\AddInterfaceByParentRector::class)->call('configure', [[\Rector\Transform\Rector\Class_\AddInterfaceByParentRector::INTERFACE_BY_PARENT => ['League\\Event\\Event' => 'League\\Event\\HasEventName', 'League\\Event\\AbstractListener' => 'League\\Event\\Listener']]]);
    $services->set(\Rector\Removing\Rector\Class_\RemoveInterfacesRector::class)->call('configure', [[\Rector\Removing\Rector\Class_\RemoveInterfacesRector::INTERFACES_TO_REMOVE => ['League\\Event\\EventInterface']]]);
    $services->set(\Rector\Removing\Rector\Class_\RemoveParentRector::class)->call('configure', [[\Rector\Removing\Rector\Class_\RemoveParentRector::PARENT_TYPES_TO_REMOVE => ['League\\Event\\AbstractEvent', 'League\\Event\\Event', 'League\\Event\\AbstractListener']]]);
    $services->set(\Rector\LeagueEvent\Rector\MethodCall\DispatchStringToObjectRector::class);
};
