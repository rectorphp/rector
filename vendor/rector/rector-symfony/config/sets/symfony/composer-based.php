<?php

declare (strict_types=1);
namespace RectorPrefix202608;

use Rector\Config\RectorConfig;
use Rector\Symfony\CodeQuality\Rector\AttributeGroup\SingleConditionSecurityAttributeToIsGrantedRector;
use Rector\Symfony\CodeQuality\Rector\Class_\SplitAndSecurityAttributeToIsGrantedRector;
use Rector\Symfony\Symfony42\Rector\New_\RootNodeTreeBuilderRector;
use Rector\Symfony\Symfony42\Rector\New_\StringToArrayArgumentProcessRector;
use Rector\Symfony\Symfony43\Rector\ClassMethod\EventDispatcherParentConstructRector;
use Rector\Symfony\Symfony43\Rector\MethodCall\MakeDispatchFirstArgumentEventRector;
use Rector\Symfony\Symfony44\Rector\ClassMethod\ConsoleExecuteReturnIntRector;
use Rector\Symfony\Symfony51\Rector\Class_\LogoutHandlerToLogoutEventSubscriberRector;
use Rector\Symfony\Symfony51\Rector\Class_\LogoutSuccessHandlerToLogoutEventSubscriberRector;
use Rector\Symfony\Symfony51\Rector\ClassMethod\CommandConstantReturnCodeRector;
use Rector\Symfony\Symfony52\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector;
use Rector\Symfony\Symfony52\Rector\MethodCall\ReflectionExtractorEnableMagicCallExtractorRector;
use Rector\Symfony\Symfony52\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector;
use Rector\Symfony\Symfony52\Rector\New_\PropertyAccessorCreationBooleanToFlagsRector;
use Rector\Symfony\Symfony52\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector;
use Rector\Symfony\Symfony61\Rector\StaticPropertyFetch\ErrorNamesPropertyToConstantRector;
use Rector\Symfony\Symfony62\Rector\Class_\SecurityAttributeToIsGrantedAttributeRector;
use Rector\Symfony\Symfony62\Rector\ClassMethod\ClassMethod\ArgumentValueResolverToValueResolverRector;
use Rector\Symfony\Symfony63\Rector\Class_\ParamAndEnvAttributeRector;
use Rector\Symfony\Symfony63\Rector\Class_\SignalableCommandInterfaceReturnTypeRector;
/**
 * Rules bound to the Symfony package version installed in the analysed project,
 * as not every attribute, class and method exists in every Symfony version.
 *
 * Thanks to the composer package constraint, these rules can be registered once here, instead of being
 * repeated in every Symfony version set to cover a direct upgrade from an older version.
 */
return static function (RectorConfig $rectorConfig): void {
    // each of these rules declares the exact Symfony package and the version its target API was added in,
    // @see ComposerPackageConstraintInterface
    $rectorConfig->rules([
        // symfony/config 4.2
        RootNodeTreeBuilderRector::class,
        // symfony/process 4.2
        StringToArrayArgumentProcessRector::class,
        // symfony/event-dispatcher 4.3
        MakeDispatchFirstArgumentEventRector::class,
        EventDispatcherParentConstructRector::class,
        // symfony/console 4.4 and 5.1
        ConsoleExecuteReturnIntRector::class,
        CommandConstantReturnCodeRector::class,
        // symfony/security-http 5.1
        LogoutHandlerToLogoutEventSubscriberRector::class,
        LogoutSuccessHandlerToLogoutEventSubscriberRector::class,
        // symfony/* 5.2
        DefinitionAliasSetPrivateToSetPublicRector::class,
        ReflectionExtractorEnableMagicCallExtractorRector::class,
        ValidatorBuilderEnableAnnotationMappingRector::class,
        PropertyAccessorCreationBooleanToFlagsRector::class,
        BinaryFileResponseCreateToNewInstanceRector::class,
        // symfony/validator 6.1
        ErrorNamesPropertyToConstantRector::class,
        // attributes added in Symfony 6.2
        SecurityAttributeToIsGrantedAttributeRector::class,
        SingleConditionSecurityAttributeToIsGrantedRector::class,
        SplitAndSecurityAttributeToIsGrantedRector::class,
        ArgumentValueResolverToValueResolverRector::class,
        // symfony/dependency-injection and symfony/console 6.3
        ParamAndEnvAttributeRector::class,
        SignalableCommandInterfaceReturnTypeRector::class,
    ]);
};
