<?php

declare (strict_types=1);
namespace RectorPrefix202608;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\Arguments\NodeAnalyzer\ArgumentAddingScope;
use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Rector\Renaming\Rector\Class_\RenameAttributeRector;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\Rector\String_\RenameStringRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey;
use Rector\Renaming\ValueObject\RenameAttribute;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Renaming\ValueObject\RenameProperty;
use Rector\StaticTypeMapper\ValueObject\Type\SimpleStaticType;
use Rector\Symfony\CodeQuality\Rector\AttributeGroup\SingleConditionSecurityAttributeToIsGrantedRector;
use Rector\Symfony\CodeQuality\Rector\BinaryOp\RequestIsMainRector;
use Rector\Symfony\CodeQuality\Rector\Class_\LoadValidatorMetadataToAttributeRector;
use Rector\Symfony\CodeQuality\Rector\Class_\SplitAndSecurityAttributeToIsGrantedRector;
use Rector\Symfony\CodeQuality\Rector\MethodCall\ParameterBagTypedGetMethodCallRector;
use Rector\Symfony\JMS\Rector\Class_\AccessTypeAnnotationToAttributeRector;
use Rector\Symfony\JMS\Rector\Property\AccessorAnnotationToAttributeRector;
use Rector\Symfony\Symfony25\Rector\MethodCall\AddViolationToBuildViolationRector;
use Rector\Symfony\Symfony25\Rector\MethodCall\MaxLengthSymfonyFormOptionToAttrRector;
use Rector\Symfony\Symfony26\Rector\MethodCall\RedirectToRouteRector;
use Rector\Symfony\Symfony27\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector;
use Rector\Symfony\Symfony28\Rector\MethodCall\GetToConstructorInjectionRector;
use Rector\Symfony\Symfony28\Rector\StaticCall\ParseFileRector;
use Rector\Symfony\Symfony30\Rector\ClassMethod\FormTypeGetParentRector;
use Rector\Symfony\Symfony30\Rector\ClassMethod\GetRequestRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\ChangeStringCollectionOptionToConstantRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\FormTypeInstanceToClassConstRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\OptionNameRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\ReadOnlyOptionToAttributeRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\StringFormTypeToClassRector;
use Rector\Symfony\Symfony33\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector;
use Rector\Symfony\Symfony34\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector;
use Rector\Symfony\Symfony34\Rector\ClassMethod\RemoveServiceFromSensioRouteRector;
use Rector\Symfony\Symfony34\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector;
use Rector\Symfony\Symfony34\Rector\Closure\ContainerGetNameToTypeInTestsRector;
use Rector\Symfony\Symfony40\Rector\ConstFetch\ConstraintUrlOptionRector;
use Rector\Symfony\Symfony40\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector;
use Rector\Symfony\Symfony40\Rector\MethodCall\FormIsValidRector;
use Rector\Symfony\Symfony40\Rector\MethodCall\VarDumperTestTraitMethodArgsRector;
use Rector\Symfony\Symfony42\Rector\New_\RootNodeTreeBuilderRector;
use Rector\Symfony\Symfony42\Rector\New_\StringToArrayArgumentProcessRector;
use Rector\Symfony\Symfony43\Rector\ClassMethod\EventDispatcherParentConstructRector;
use Rector\Symfony\Symfony43\Rector\MethodCall\ConvertRenderTemplateShortNotationToBundleSyntaxRector;
use Rector\Symfony\Symfony43\Rector\MethodCall\GetCurrencyBundleMethodCallsToIntlRector;
use Rector\Symfony\Symfony43\Rector\MethodCall\MakeDispatchFirstArgumentEventRector;
use Rector\Symfony\Symfony43\Rector\StmtsAwareInterface\TwigBundleFilesystemLoaderToTwigRector;
use Rector\Symfony\Symfony44\Rector\ClassMethod\ConsoleExecuteReturnIntRector;
use Rector\Symfony\Symfony44\Rector\MethodCall\AuthorizationCheckerIsGrantedExtractorRector;
use Rector\Symfony\Symfony44\Rector\MethodCall\WebLinkStringRelationsToConstantsRector;
use Rector\Symfony\Symfony51\Rector\Class_\LogoutHandlerToLogoutEventSubscriberRector;
use Rector\Symfony\Symfony51\Rector\Class_\LogoutSuccessHandlerToLogoutEventSubscriberRector;
use Rector\Symfony\Symfony51\Rector\ClassMethod\CommandConstantReturnCodeRector;
use Rector\Symfony\Symfony51\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector;
use Rector\Symfony\Symfony52\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector;
use Rector\Symfony\Symfony52\Rector\MethodCall\FormBuilderSetDataMapperRector;
use Rector\Symfony\Symfony52\Rector\MethodCall\ReflectionExtractorEnableMagicCallExtractorRector;
use Rector\Symfony\Symfony52\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector;
use Rector\Symfony\Symfony52\Rector\New_\PropertyAccessorCreationBooleanToFlagsRector;
use Rector\Symfony\Symfony52\Rector\New_\PropertyPathMapperToDataMapperRector;
use Rector\Symfony\Symfony52\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector;
use Rector\Symfony\Symfony53\Rector\StaticPropertyFetch\KernelTestCaseContainerPropertyDeprecationRector;
use Rector\Symfony\Symfony60\Rector\FuncCall\ContainerInterfaceServiceToServiceContainerRector;
use Rector\Symfony\Symfony60\Rector\MethodCall\GetHelperControllerToServiceRector;
use Rector\Symfony\Symfony61\Rector\Attribute\RouteRequirementStringToConstantRector;
use Rector\Symfony\Symfony61\Rector\Class_\CommandConfigureToAttributeRector;
use Rector\Symfony\Symfony61\Rector\Class_\CommandPropertyToAttributeRector;
use Rector\Symfony\Symfony61\Rector\Class_\MagicClosureTwigExtensionToNativeMethodsRector;
use Rector\Symfony\Symfony61\Rector\StaticPropertyFetch\ErrorNamesPropertyToConstantRector;
use Rector\Symfony\Symfony62\Rector\Class_\MessageHandlerInterfaceToAttributeRector;
use Rector\Symfony\Symfony62\Rector\Class_\MessageSubscriberInterfaceToAttributeRector;
use Rector\Symfony\Symfony62\Rector\Class_\SecurityAttributeToIsGrantedAttributeRector;
use Rector\Symfony\Symfony62\Rector\ClassMethod\ClassMethod\ArgumentValueResolverToValueResolverRector;
use Rector\Symfony\Symfony62\Rector\ClassMethod\ParamConverterAttributeToMapEntityAttributeRector;
use Rector\Symfony\Symfony63\Rector\Class_\ParamAndEnvAttributeRector;
use Rector\Symfony\Symfony63\Rector\Class_\SignalableCommandInterfaceReturnTypeRector;
use Rector\Symfony\Symfony72\Rector\StmtsAwareInterface\PushRequestToRequestStackConstructorRector;
use Rector\Symfony\Symfony73\Rector\Class_\AddVoteArgumentToVoteOnAttributeRector;
use Rector\Symfony\Symfony73\Rector\Class_\AuthorizationCheckerToAccessDecisionManagerInVoterRector;
use Rector\Symfony\Symfony73\Rector\Class_\CommandDefaultNameAndDescriptionToAsCommandAttributeRector;
use Rector\Symfony\Symfony73\Rector\Class_\CommandHelpToAttributeRector;
use Rector\Symfony\Symfony73\Rector\Class_\ConstraintOptionsToNamedArgumentsRector;
use Rector\Symfony\Symfony73\Rector\Class_\GetFiltersAndFunctionsToAsTwigAttributeRector;
use Rector\Symfony\Symfony80\Rector\Class_\RemoveEraseCredentialsRector;
use Rector\Symfony\Symfony81\Rector\MethodCall\ConstraintValidatorValidateToValidateInContextRector;
use Rector\Symfony\Symfony81\Rector\MethodCall\RenameCopyOnWindowsOptionToFollowSymlinksRector;
use Rector\Symfony\Symfony81\Rector\New_\RemoveEraseCredentialsFromAuthenticatorManagerRector;
use Rector\Symfony\Symfony81\Rector\StaticCall\AddFormatArgumentToIsValidRector;
use Rector\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector;
use Rector\Transform\Rector\ClassMethod\WrapReturnRector;
use Rector\Transform\Rector\StaticCall\StaticCallToNewRector;
use Rector\Transform\ValueObject\AttributeKeyToClassConstFetch;
use Rector\Transform\ValueObject\StaticCallToNew;
use Rector\Transform\ValueObject\WrapReturn;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Rector\Util\Reflection\PrivatesAccessor;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\Visibility;
use Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;
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
        // symfony/form 2.5
        MaxLengthSymfonyFormOptionToAttrRector::class,
        // symfony/validator 2.5
        AddViolationToBuildViolationRector::class,
        // symfony/framework-bundle 2.5
        GetRequestRector::class,
        // symfony/framework-bundle 2.6
        RedirectToRouteRector::class,
        // symfony/form 2.7
        ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector::class,
        // symfony/framework-bundle 2.8
        GetToConstructorInjectionRector::class,
        // symfony/yaml 2.8
        ParseFileRector::class,
        // symfony/form 3.0
        ChangeStringCollectionOptionToConstantRector::class,
        FormTypeGetParentRector::class,
        OptionNameRector::class,
        ReadOnlyOptionToAttributeRector::class,
        StringFormTypeToClassRector::class,
        // symfony/framework-bundle 3.0
        FormTypeInstanceToClassConstRector::class,
        // symfony/console 3.3
        ConsoleExceptionToErrorEventConstantRector::class,
        // symfony/dependency-injection 3.4
        ContainerGetNameToTypeInTestsRector::class,
        // symfony/routing 3.4
        MergeMethodAnnotationToRouteAnnotationRector::class,
        RemoveServiceFromSensioRouteRector::class,
        ReplaceSensioRouteAnnotationWithSymfonyRector::class,
        // symfony/dependency-injection 4.0
        ContainerBuilderCompileEnvArgumentRector::class,
        // symfony/form 4.0
        FormIsValidRector::class,
        // symfony/validator 4.0
        ConstraintUrlOptionRector::class,
        // symfony/var-dumper 4.0
        VarDumperTestTraitMethodArgsRector::class,
        // symfony/config 4.2
        RootNodeTreeBuilderRector::class,
        // symfony/process 4.2
        StringToArrayArgumentProcessRector::class,
        // symfony/event-dispatcher 4.3
        EventDispatcherParentConstructRector::class,
        MakeDispatchFirstArgumentEventRector::class,
        // symfony/framework-bundle 4.3
        ConvertRenderTemplateShortNotationToBundleSyntaxRector::class,
        // symfony/intl 4.3
        GetCurrencyBundleMethodCallsToIntlRector::class,
        // symfony/twig-bundle 4.3
        TwigBundleFilesystemLoaderToTwigRector::class,
        // symfony/console 4.4
        ConsoleExecuteReturnIntRector::class,
        // symfony/security-core 4.4
        AuthorizationCheckerIsGrantedExtractorRector::class,
        // symfony/web-link 4.4
        WebLinkStringRelationsToConstantsRector::class,
        // symfony/console 5.1
        CommandConstantReturnCodeRector::class,
        // symfony/framework-bundle 5.1
        RouteCollectionBuilderToRoutingConfiguratorRector::class,
        // symfony/security-http 5.1
        LogoutHandlerToLogoutEventSubscriberRector::class,
        LogoutSuccessHandlerToLogoutEventSubscriberRector::class,
        // symfony/http-foundation 5.1
        ParameterBagTypedGetMethodCallRector::class,
        // symfony/dependency-injection 5.2
        DefinitionAliasSetPrivateToSetPublicRector::class,
        // symfony/form 5.2
        FormBuilderSetDataMapperRector::class,
        PropertyPathMapperToDataMapperRector::class,
        // symfony/http-foundation 5.2
        BinaryFileResponseCreateToNewInstanceRector::class,
        // symfony/property-access 5.2
        PropertyAccessorCreationBooleanToFlagsRector::class,
        // symfony/property-info 5.2
        ReflectionExtractorEnableMagicCallExtractorRector::class,
        // symfony/validator 5.2
        LoadValidatorMetadataToAttributeRector::class,
        ValidatorBuilderEnableAnnotationMappingRector::class,
        // symfony/framework-bundle 5.3
        KernelTestCaseContainerPropertyDeprecationRector::class,
        // symfony/http-kernel 5.3
        RequestIsMainRector::class,
        // symfony/dependency-injection 6.0
        ContainerInterfaceServiceToServiceContainerRector::class,
        // symfony/framework-bundle 6.0
        GetHelperControllerToServiceRector::class,
        // symfony/console 6.1
        CommandConfigureToAttributeRector::class,
        CommandPropertyToAttributeRector::class,
        // symfony/routing 6.1
        RouteRequirementStringToConstantRector::class,
        // symfony/twig-bridge 6.1
        MagicClosureTwigExtensionToNativeMethodsRector::class,
        // symfony/validator 6.1
        ErrorNamesPropertyToConstantRector::class,
        // symfony/doctrine-bridge 6.2
        ParamConverterAttributeToMapEntityAttributeRector::class,
        // symfony/http-kernel 6.2
        ArgumentValueResolverToValueResolverRector::class,
        // symfony/messenger 6.2
        MessageHandlerInterfaceToAttributeRector::class,
        MessageSubscriberInterfaceToAttributeRector::class,
        // symfony/security-http 6.2
        SecurityAttributeToIsGrantedAttributeRector::class,
        SingleConditionSecurityAttributeToIsGrantedRector::class,
        SplitAndSecurityAttributeToIsGrantedRector::class,
        // symfony/console 6.3
        SignalableCommandInterfaceReturnTypeRector::class,
        // symfony/dependency-injection 6.3
        ParamAndEnvAttributeRector::class,
        // symfony/http-foundation 7.2
        PushRequestToRequestStackConstructorRector::class,
        // symfony/console 7.3
        CommandDefaultNameAndDescriptionToAsCommandAttributeRector::class,
        CommandHelpToAttributeRector::class,
        // symfony/security-core 7.3
        AddVoteArgumentToVoteOnAttributeRector::class,
        AuthorizationCheckerToAccessDecisionManagerInVoterRector::class,
        // symfony/validator 7.3
        ConstraintOptionsToNamedArgumentsRector::class,
        // symfony/security-core 8.0
        RemoveEraseCredentialsRector::class,
        // symfony/filesystem 8.1
        RenameCopyOnWindowsOptionToFollowSymlinksRector::class,
        // symfony/security-http 8.1
        RemoveEraseCredentialsFromAuthenticatorManagerRector::class,
        // symfony/uid 8.1
        AddFormatArgumentToIsValidRector::class,
        // symfony/validator 8.1
        ConstraintValidatorValidateToValidateInContextRector::class,
        // twig/twig 3.21 + symfony/twig-bridge 7.3
        GetFiltersAndFunctionsToAsTwigAttributeRector::class,
        // jms/serializer 3.14
        AccessTypeAnnotationToAttributeRector::class,
        AccessorAnnotationToAttributeRector::class,
    ]);
    // shared types used by the configuration below
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $browserKitResponseType = new ObjectType('Symfony\Component\BrowserKit\Response');
    $commandType = new ObjectType('Symfony\Component\Console\Command\Command');
    $configurationType = new ObjectType('Symfony\Component\Config\Definition\ConfigurationInterface');
    $httpFoundationResponseType = new ObjectType('Symfony\Component\HttpFoundation\Response');
    $iterableType = new IterableType(new MixedType(), new MixedType());
    $nullableArrayType = new UnionType([new NullType(), $arrayType]);
    $nullableBooleanType = new UnionType([new NullType(), new BooleanType()]);
    $nullableIntegerType = new UnionType([new NullType(), new IntegerType()]);
    $nullableStringType = new UnionType([new NullType(), new StringType()]);
    $nullableValueGuessType = new UnionType([new NullType(), new ObjectType('Symfony\Component\Form\Guess\ValueGuess')]);
    $routeCollectionType = new ObjectType('Symfony\Component\Routing\RouteCollection');
    $scalarTypes = [$arrayType, new BooleanType(), new StringType(), new IntegerType(), new FloatType(), new NullType()];
    $typeGuessType = new ObjectType('Symfony\Component\Form\Guess\TypeGuess');
    $scalarArrayObjectUnionedTypes = \array_merge($scalarTypes, [new ObjectType('ArrayObject')]);
    // cannot be crated with \PHPStan\Type\UnionTypeHelper::sortTypes() as ObjectType requires a class reflection we do not have here
    $unionTypeReflectionClass = new \ReflectionClass(UnionType::class);
    /** @var UnionType $scalarArrayObjectUnionType */
    $scalarArrayObjectUnionType = $unionTypeReflectionClass->newInstanceWithoutConstructor();
    $privatesAccessor = new PrivatesAccessor();
    $privatesAccessor->setPrivateProperty($scalarArrayObjectUnionType, 'types', $scalarArrayObjectUnionedTypes);
    // symfony/form 2.7
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // @see https://symfony.com/blog/new-in-symfony-2-7-form-and-validator-updates#deprecated-setdefaultoptions-in-favor-of-configureoptions
        new MethodCallRename('Symfony\Component\Form\AbstractType', 'setDefaultOptions', 'configureOptions'),
    ], 'symfony/form', '>=2.7');
    // symfony/options-resolver 2.7
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\OptionsResolver\OptionsResolverInterface' => 'Symfony\Component\OptionsResolver\OptionsResolver'], 'symfony/options-resolver', '>=2.7');
    // symfony/routing 2.8
    $rectorConfig->ruleWithConfigurationComposerVersionBound(ReplaceArgumentDefaultValueRector::class, [
        // @see https://github.com/symfony/symfony/commit/912fc4de8fd6de1e5397be4a94d39091423e5188
        new ReplaceArgumentDefaultValue('Symfony\Component\Routing\Generator\UrlGeneratorInterface', 'generate', 2, \true, 'Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL'),
        new ReplaceArgumentDefaultValue('Symfony\Component\Routing\Generator\UrlGeneratorInterface', 'generate', 2, \false, 'Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_PATH'),
        new ReplaceArgumentDefaultValue('Symfony\Component\Routing\Generator\UrlGeneratorInterface', 'generate', 2, 'relative', 'Symfony\Component\Routing\Generator\UrlGeneratorInterface::RELATIVE_PATH'),
        new ReplaceArgumentDefaultValue('Symfony\Component\Routing\Generator\UrlGeneratorInterface', 'generate', 2, 'network', 'Symfony\Component\Routing\Generator\UrlGeneratorInterface::NETWORK_PATH'),
    ], 'symfony/routing', '>=2.8');
    // symfony/symfony 2.8
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/blob/2.8/UPGRADE-2.8.md#security
        'Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface' => 'Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface',
        'Symfony\Component\Security\Core\Authentication\SimpleFormAuthenticatorInterface' => 'Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface',
    ], 'symfony/symfony', '>=2.8');
    // symfony/bridge-swift-mailer 3.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // swift mailer
        'Symfony\Bridge\Swiftmailer\DataCollector\MessageDataCollector' => 'Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector',
    ], 'symfony/bridge-swift-mailer', '>=3.0');
    // symfony/class-loader 3.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader' => 'Symfony\Component\ClassLoader\ClassLoader'], 'symfony/class-loader', '>=3.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader', 'registerNamespaces', 'addPrefixes'), new MethodCallRename('Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader', 'registerPrefixes', 'addPrefixes'), new MethodCallRename('Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader', 'registerNamespace', 'addPrefix'), new MethodCallRename('Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader', 'registerPrefix', 'addPrefix'), new MethodCallRename('Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader', 'getNamespaces', 'getPrefixes'), new MethodCallRename('Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader', 'getNamespaceFallbacks', 'getFallbackDirs'), new MethodCallRename('Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader', 'getPrefixFallbacks', 'getFallbackDirs')], 'symfony/class-loader', '>=3.0');
    // symfony/console 3.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // console
        'Symfony\Component\Console\Helper\ProgressHelper' => 'Symfony\Component\Console\Helper\ProgressBar',
    ], 'symfony/console', '>=3.0');
    // symfony/form 3.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassConstFetchRector::class, [new RenameClassConstFetch('Symfony\Component\Form\FormEvents', 'PRE_BIND', 'PRE_SUBMIT'), new RenameClassConstFetch('Symfony\Component\Form\FormEvents', 'BIND', 'SUBMIT'), new RenameClassConstFetch('Symfony\Component\Form\FormEvents', 'POST_BIND', 'POST_SUBMIT'), new RenameClassConstFetch('Symfony\Component\Form\Extension\Core\DataTransformer', 'ROUND_HALFEVEN', 'ROUND_HALF_EVEN'), new RenameClassConstFetch('Symfony\Component\Form\Extension\Core\DataTransformer', 'ROUND_HALFUP', 'ROUND_HALF_UP'), new RenameClassConstFetch('Symfony\Component\Form\Extension\Core\DataTransformer', 'ROUND_HALFDOWN', 'ROUND_HALF_DOWN')], 'symfony/form', '>=3.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\Form\Util\VirtualFormAwareIterator' => 'Symfony\Component\Form\Util\InheritDataAwareIterator', 'Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase' => 'Symfony\Component\Form\Test\TypeTestCase', 'Symfony\Component\Form\Tests\FormIntegrationTestCase' => 'Symfony\Component\Form\Test\FormIntegrationTestCase', 'Symfony\Component\Form\Tests\FormPerformanceTestCase' => 'Symfony\Component\Form\Test\FormPerformanceTestCase', 'Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface' => 'Symfony\Component\Form\ChoiceList\ChoiceListInterface', 'Symfony\Component\Form\Extension\Core\View\ChoiceView' => 'Symfony\Component\Form\ChoiceList\View\ChoiceView', 'Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface' => 'Symfony\Component\Security\Csrf\CsrfTokenManagerInterface', 'Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList' => 'Symfony\Component\Form\ChoiceList\ArrayChoiceList', 'Symfony\Component\Form\Extension\Core\ChoiceList\LazyChoiceList' => 'Symfony\Component\Form\ChoiceList\LazyChoiceList', 'Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList' => 'Symfony\Component\Form\ChoiceList\ArrayChoiceList', 'Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList' => 'Symfony\Component\Form\ChoiceList\ArrayChoiceList', 'Symfony\Component\Form\ChoiceList\ArrayKeyChoiceList' => 'Symfony\Component\Form\ChoiceList\ArrayChoiceList'], 'symfony/form', '>=3.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\Form\AbstractType', 'getName', 'getBlockPrefix'), new MethodCallRename('Symfony\Component\Form\FormTypeInterface', 'getName', 'getBlockPrefix'), new MethodCallRename('Symfony\Component\Form\FormTypeInterface', 'setDefaultOptions', 'configureOptions'), new MethodCallRename('Symfony\Component\Form\ResolvedFormTypeInterface', 'getName', 'getBlockPrefix'), new MethodCallRename('Symfony\Component\Form\AbstractTypeExtension', 'setDefaultOptions', 'configureOptions'), new MethodCallRename('Symfony\Component\Form\Form', 'bind', 'submit'), new MethodCallRename('Symfony\Component\Form\Form', 'isBound', 'isSubmitted')], 'symfony/form', '>=3.0');
    // symfony/http-kernel 3.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\HttpKernel\Debug\ErrorHandler' => 'Symfony\Component\Debug\ErrorHandler', 'Symfony\Component\HttpKernel\Debug\ExceptionHandler' => 'Symfony\Component\Debug\ExceptionHandler', 'Symfony\Component\HttpKernel\Exception\FatalErrorException' => 'Symfony\Component\Debug\Exception\FatalErrorException', 'Symfony\Component\HttpKernel\Exception\FlattenException' => 'Symfony\Component\Debug\Exception\FlattenException', 'Symfony\Component\HttpKernel\Log\LoggerInterface' => 'Psr\Log\LoggerInterface', 'Symfony\Component\HttpKernel\DependencyInjection\RegisterListenersPass' => 'Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass', 'Symfony\Component\HttpKernel\Log\NullLogger' => 'Psr\Log\LoggerInterface'], 'symfony/http-kernel', '>=3.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\HttpKernel\Log\LoggerInterface', 'emerg', 'emergency'), new MethodCallRename('Symfony\Component\HttpKernel\Log\LoggerInterface', 'crit', 'critical'), new MethodCallRename('Symfony\Component\HttpKernel\Log\LoggerInterface', 'err', 'error'), new MethodCallRename('Symfony\Component\HttpKernel\Log\LoggerInterface', 'warn', 'warning'), new MethodCallRename('Symfony\Component\HttpKernel\Log\NullLogger', 'emerg', 'emergency'), new MethodCallRename('Symfony\Component\HttpKernel\Log\NullLogger', 'crit', 'critical'), new MethodCallRename('Symfony\Component\HttpKernel\Log\NullLogger', 'err', 'error'), new MethodCallRename('Symfony\Component\HttpKernel\Log\NullLogger', 'warn', 'warning')], 'symfony/http-kernel', '>=3.0');
    // symfony/monolog-bridge 3.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Bridge\Monolog\Logger' => 'Psr\Log\LoggerInterface'], 'symfony/monolog-bridge', '>=3.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Bridge\Monolog\Logger', 'emerg', 'emergency'), new MethodCallRename('Symfony\Bridge\Monolog\Logger', 'crit', 'critical'), new MethodCallRename('Symfony\Bridge\Monolog\Logger', 'err', 'error'), new MethodCallRename('Symfony\Bridge\Monolog\Logger', 'warn', 'warning')], 'symfony/monolog-bridge', '>=3.0');
    // symfony/process 3.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\Process\Process', 'setStdin', 'setInput'), new MethodCallRename('Symfony\Component\Process\Process', 'getStdin', 'getInput')], 'symfony/process', '>=3.0');
    // symfony/property-access 3.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\PropertyAccess\PropertyAccess', 'getPropertyAccessor', 'createPropertyAccessor')], 'symfony/property-access', '>=3.0');
    // symfony/security 3.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter' => 'Symfony\Component\Security\Core\Authorization\Voter\Voter'], 'symfony/security', '>=3.0');
    // symfony/translation 3.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\Translation\Dumper\FileDumper', 'format', 'formatCatalogue'), new MethodCallRename('Symfony\Component\Translation\Translator', 'getMessages', 'getCatalogue')], 'symfony/translation', '>=3.0');
    // symfony/twig-bundle 3.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Bundle\TwigBundle\TwigDefaultEscapingStrategy' => 'Twig_FileExtensionEscapingStrategy'], 'symfony/twig-bundle', '>=3.0');
    // symfony/validator 3.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\Validator\Constraints\Collection\Optional' => 'Symfony\Component\Validator\Constraints\Optional', 'Symfony\Component\Validator\Constraints\Collection\Required' => 'Symfony\Component\Validator\Constraints\Required', 'Symfony\Component\Validator\MetadataInterface' => 'Symfony\Component\Validator\Mapping\MetadataInterface', 'Symfony\Component\Validator\PropertyMetadataInterface' => 'Symfony\Component\Validator\Mapping\PropertyMetadataInterface', 'Symfony\Component\Validator\PropertyMetadataContainerInterface' => 'Symfony\Component\Validator\Mapping\ClassMetadataInterface', 'Symfony\Component\Validator\ClassBasedInterface' => 'Symfony\Component\Validator\Mapping\ClassMetadataInterface', 'Symfony\Component\Validator\Mapping\ElementMetadata' => 'Symfony\Component\Validator\Mapping\GenericMetadata', 'Symfony\Component\Validator\ExecutionContextInterface' => 'Symfony\Component\Validator\Context\ExecutionContextInterface', 'Symfony\Component\Validator\Mapping\ClassMetadataFactory' => 'Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory', 'Symfony\Component\Validator\Mapping\MetadataFactoryInterface' => 'Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface'], 'symfony/validator', '>=3.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\Validator\ConstraintViolationInterface', 'getMessageParameters', 'getParameters'), new MethodCallRename('Symfony\Component\Validator\ConstraintViolationInterface', 'getMessagePluralization', 'getPlural'), new MethodCallRename('Symfony\Component\Validator\ConstraintViolation', 'getMessageParameters', 'getParameters'), new MethodCallRename('Symfony\Component\Validator\ConstraintViolation', 'getMessagePluralization', 'getPlural')], 'symfony/validator', '>=3.0');
    // symfony/yaml 3.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(ReplaceArgumentDefaultValueRector::class, [new ReplaceArgumentDefaultValue('Symfony\Component\Yaml\Yaml', 'parse', 1, [\false, \false, \true], 'Symfony\Component\Yaml\Yaml::PARSE_OBJECT_FOR_MAP'), new ReplaceArgumentDefaultValue('Symfony\Component\Yaml\Yaml', 'parse', 1, [\false, \true], 'Symfony\Component\Yaml\Yaml::PARSE_OBJECT'), new ReplaceArgumentDefaultValue('Symfony\Component\Yaml\Yaml', 'parse', 1, \true, 'Symfony\Component\Yaml\Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE'), new ReplaceArgumentDefaultValue('Symfony\Component\Yaml\Yaml', 'parse', 1, \false, 0), new ReplaceArgumentDefaultValue('Symfony\Component\Yaml\Yaml', 'dump', 3, [\false, \true], 'Symfony\Component\Yaml\Yaml::DUMP_OBJECT'), new ReplaceArgumentDefaultValue('Symfony\Component\Yaml\Yaml', 'dump', 3, \true, 'Symfony\Component\Yaml\Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE')], 'symfony/yaml', '>=3.1');
    // symfony/dependency-injection 3.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(ArgumentAdderRector::class, [new ArgumentAdder('Symfony\Component\DependencyInjection\ContainerBuilder', 'addCompilerPass', 2, 'priority', 0)], 'symfony/dependency-injection', '>=3.2');
    // symfony/http-foundation 3.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(ReplaceArgumentDefaultValueRector::class, [new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', '__construct', 8, 'lax', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_LAX'), new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', '__construct', 8, 'strict', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_STRICT')], 'symfony/http-foundation', '>=3.2');
    // symfony/console 3.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // console
        'Symfony\Component\Console\Event\ConsoleExceptionEvent' => 'Symfony\Component\Console\Event\ConsoleErrorEvent',
    ], 'symfony/console', '>=3.3');
    // symfony/debug 3.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // debug
        'Symfony\Component\Debug\Exception\ContextErrorException' => 'ErrorException',
    ], 'symfony/debug', '>=3.3');
    // symfony/dependency-injection 3.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(ArgumentAdderRector::class, [new ArgumentAdder('Symfony\Component\DependencyInjection\ContainerBuilder', 'compile', 0, 'resolveEnvPlaceholders', \false), new ArgumentAdder('Symfony\Component\DependencyInjection\ContainerBuilder', 'addCompilerPass', 2, 'priority', 0), new ArgumentAdder('Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraph', 'connect', 6, 'weak', \false)], 'symfony/dependency-injection', '>=3.3');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\DependencyInjection\DefinitionDecorator' => 'Symfony\Component\DependencyInjection\ChildDefinition'], 'symfony/dependency-injection', '>=3.3');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\DependencyInjection\Container', 'isFrozen', 'isCompiled')], 'symfony/dependency-injection', '>=3.3');
    // symfony/framework-bundle 3.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        # framework bundle
        'Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\AddConsoleCommandPass' => 'Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass',
        'Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\SerializerPass' => 'Symfony\Component\Serializer\DependencyInjection\SerializerPass',
        'Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\FormPass' => 'Symfony\Component\Form\DependencyInjection\FormPass',
        'Symfony\Bundle\FrameworkBundle\EventListener\SessionListener' => 'Symfony\Component\HttpKernel\EventListener\SessionListener',
        'Symfony\Bundle\FrameworkBundle\EventListener\TestSessionListener' => 'Symfony\Component\HttpKernel\EventListener\TestSessionListener',
        'Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\ConfigCachePass' => 'Symfony\Component\Config\DependencyInjection\ConfigCachePass',
        'Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\PropertyInfoPass' => 'Symfony\Component\PropertyInfo\DependencyInjection\PropertyInfoPass',
    ], 'symfony/framework-bundle', '>=3.3');
    // symfony/yaml 3.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(ArgumentRemoverRector::class, [new ArgumentRemover('Symfony\Component\Yaml\Yaml', 'parse', 2, ['Symfony\Component\Yaml\Yaml::PARSE_KEYS_AS_STRINGS'])], 'symfony/yaml', '>=3.4');
    // symfony/process 4.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\Process\ProcessBuilder' => 'Symfony\Component\Process\Process'], 'symfony/process', '>=4.0');
    // symfony/validator 4.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest' => 'Symfony\Component\Validator\Test\ConstraintValidatorTestCase'], 'symfony/validator', '>=4.0');
    // symfony/console 4.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        # https://github.com/symfony/symfony/commit/463f986c28a497571967e37c1314e9911f1ef6ba
        new MethodCallRename('Symfony\Component\Console\Helper\TableStyle', 'setHorizontalBorderChar', 'setHorizontalBorderChars'),
        # https://github.com/symfony/symfony/commit/463f986c28a497571967e37c1314e9911f1ef6ba
        new MethodCallRename('Symfony\Component\Console\Helper\TableStyle', 'setVerticalBorderChar', 'setVerticalBorderChars'),
        # https://github.com/symfony/symfony/commit/463f986c28a497571967e37c1314e9911f1ef6ba
        new MethodCallRename('Symfony\Component\Console\Helper\TableStyle', 'setCrossingChar', 'setDefaultCrossingChar'),
        # https://github.com/symfony/symfony/commit/463f986c28a497571967e37c1314e9911f1ef6ba
        new MethodCallRenameWithArrayKey(
            'Symfony\Component\Console\Helper\TableStyle',
            'getVerticalBorderChar',
            # special case to "getVerticalBorderChar" → "getBorderChars()[3]"
            'getBorderChars',
            3
        ),
        # https://github.com/symfony/symfony/commit/463f986c28a497571967e37c1314e9911f1ef6ba
        new MethodCallRenameWithArrayKey('Symfony\Component\Console\Helper\TableStyle', 'getHorizontalBorderChar', 'getBorderChars', 2),
    ], 'symfony/console', '>=4.1');
    // symfony/framework-bundle 4.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        # https://github.com/symfony/symfony/commit/07dd09db59e2f2a86a291d00d978169d9059e307
        'Symfony\Bundle\FrameworkBundle\DataCollector\RequestDataCollector' => 'Symfony\Component\HttpKernel\DataCollector\RequestDataCollector',
    ], 'symfony/framework-bundle', '>=4.1');
    // symfony/http-foundation 4.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\HttpFoundation\File\UploadedFile', 'getClientSize', 'getSize')], 'symfony/http-foundation', '>=4.1');
    // symfony/workflow 4.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\Workflow\SupportStrategy\SupportStrategyInterface' => 'Symfony\Component\Workflow\SupportStrategy\WorkflowSupportStrategyInterface', 'Symfony\Component\Workflow\SupportStrategy\ClassInstanceSupportStrategy' => 'Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy'], 'symfony/workflow', '>=4.1');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\Workflow\DefinitionBuilder', 'reset', 'clear'), new MethodCallRename('Symfony\Component\Workflow\DefinitionBuilder', 'add', 'addWorkflow')], 'symfony/workflow', '>=4.1');
    // symfony/cache 4.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\Cache\CacheItem', 'getPreviousTags', 'getMetadata')], 'symfony/cache', '>=4.2');
    // symfony/dom-crawler 4.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(ArgumentAdderRector::class, [new ArgumentAdder('Symfony\Component\DomCrawler\Crawler', 'children', 0, null, null, null, ArgumentAddingScope::SCOPE_METHOD_CALL)], 'symfony/dom-crawler', '>=4.2');
    // symfony/finder 4.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(ArgumentAdderRector::class, [new ArgumentAdder('Symfony\Component\Finder\Finder', 'sortByName', 0, null, \false, null, ArgumentAddingScope::SCOPE_METHOD_CALL)], 'symfony/finder', '>=4.2');
    // symfony/form 4.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\Form\AbstractTypeExtension', 'getExtendedTypes', $iterableType)], 'symfony/form', '>=4.2');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(ChangeMethodVisibilityRector::class, [new ChangeMethodVisibility('Symfony\Component\Form\AbstractTypeExtension', 'getExtendedTypes', Visibility::STATIC)], 'symfony/form', '>=4.2');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\Form\AbstractTypeExtension', 'getExtendedType', 'getExtendedTypes')], 'symfony/form', '>=4.2');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(WrapReturnRector::class, [new WrapReturn('Symfony\Component\Form\AbstractTypeExtension', 'getExtendedTypes', \true)], 'symfony/form', '>=4.2');
    // symfony/framework-bundle 4.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        # https://github.com/symfony/symfony/commit/a7e319d9e1316e2e18843f8ce15b67a8693e5bf9
        'Symfony\Bundle\FrameworkBundle\Controller\Controller' => 'Symfony\Bundle\FrameworkBundle\Controller\AbstractController',
    ], 'symfony/framework-bundle', '>=4.2');
    // symfony/http-foundation 4.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(ReplaceArgumentDefaultValueRector::class, [new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', MethodName::CONSTRUCT, 5, \false, null), new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', MethodName::CONSTRUCT, 8, null, 'lax'), new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', '__construct', 8, 'none', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_NONE'), new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', 'create', 8, 'none', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_NONE'), new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', 'create', 8, 'lax', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_LAX'), new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', 'create', 8, 'strict', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_STRICT')], 'symfony/http-foundation', '>=4.2');
    // symfony/http-kernel 4.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(ArgumentRemoverRector::class, [new ArgumentRemover('Symfony\Component\HttpKernel\DataCollector\ConfigDataCollector', MethodName::CONSTRUCT, 0, null), new ArgumentRemover('Symfony\Component\HttpKernel\DataCollector\ConfigDataCollector', MethodName::CONSTRUCT, 1, null)], 'symfony/http-kernel', '>=4.2');
    // symfony/monolog-bridge 4.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(ArgumentAdderRector::class, [new ArgumentAdder('Symfony\Bridge\Monolog\Processor\DebugProcessor', 'getLogs', 0, null, null, null, ArgumentAddingScope::SCOPE_METHOD_CALL), new ArgumentAdder('Symfony\Bridge\Monolog\Processor\DebugProcessor', 'countErrors', 0, 'default_value', null, null, ArgumentAddingScope::SCOPE_METHOD_CALL), new ArgumentAdder('Symfony\Bridge\Monolog\Logger', 'getLogs', 0, 'default_value', null, null, ArgumentAddingScope::SCOPE_METHOD_CALL), new ArgumentAdder('Symfony\Bridge\Monolog\Logger', 'countErrors', 0, 'default_value', null, null, ArgumentAddingScope::SCOPE_METHOD_CALL)], 'symfony/monolog-bridge', '>=4.2');
    // symfony/serializer 4.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(ArgumentAdderRector::class, [new ArgumentAdder('Symfony\Component\Serializer\Normalizer', 'handleCircularReference', 1, null, null, null, ArgumentAddingScope::SCOPE_METHOD_CALL), new ArgumentAdder('Symfony\Component\Serializer\Normalizer', 'handleCircularReference', 2, null, null, null, ArgumentAddingScope::SCOPE_METHOD_CALL)], 'symfony/serializer', '>=4.2');
    // symfony/translation 4.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\Translation\TranslatorInterface' => 'Symfony\Contracts\Translation\TranslatorInterface'], 'symfony/translation', '>=4.2');
    // symfony/browser-kit 4.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\BrowserKit\Client' => 'Symfony\Component\BrowserKit\AbstractBrowser'], 'symfony/browser-kit', '>=4.3');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\BrowserKit\Response', 'getStatus', 'getStatusCode')], 'symfony/browser-kit', '>=4.3');
    // symfony/cache 4.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        # https://github.com/symfony/symfony/pull/29236
        'Symfony\Component\Cache\Traits\ApcuTrait\ApcuCache' => 'Symfony\Component\Cache\Traits\ApcuTrait\ApcuAdapter',
        'Symfony\Component\Cache\Adapter\SimpleCacheAdapter' => 'Symfony\Component\Cache\Adapter\Psr16Adapter',
        'Symfony\Component\Cache\Simple\ArrayCache' => 'Symfony\Component\Cache\Adapter\ArrayAdapter',
        'Symfony\Component\Cache\Simple\ChainCache' => 'Symfony\Component\Cache\Adapter\ChainAdapter',
        'Symfony\Component\Cache\Simple\DoctrineCache' => 'Symfony\Component\Cache\Adapter\DoctrineAdapter',
        'Symfony\Component\Cache\Simple\FilesystemCache' => 'Symfony\Component\Cache\Adapter\FilesystemAdapter',
        'Symfony\Component\Cache\Simple\MemcachedCache' => 'Symfony\Component\Cache\Adapter\MemcachedAdapter',
        'Symfony\Component\Cache\Simple\NullCache' => 'Symfony\Component\Cache\Adapter\NullAdapter',
        'Symfony\Component\Cache\Simple\PdoCache' => 'Symfony\Component\Cache\Adapter\PdoAdapter',
        'Symfony\Component\Cache\Simple\PhpArrayCache' => 'Symfony\Component\Cache\Adapter\PhpArrayAdapter',
        'Symfony\Component\Cache\Simple\PhpFilesCache' => 'Symfony\Component\Cache\Adapter\PhpFilesAdapter',
        'Symfony\Component\Cache\Simple\RedisCache' => 'Symfony\Component\Cache\Adapter\RedisAdapter',
        'Symfony\Component\Cache\Simple\TraceableCache' => 'Symfony\Component\Cache\Adapter\TraceableAdapterCache',
        'Symfony\Component\Cache\Simple\Psr6Cache' => 'Symfony\Component\Cache\Psr16Cache',
    ], 'symfony/cache', '>=4.3');
    // symfony/event-dispatcher 4.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // has lowest priority, have to be last
        'Symfony\Component\EventDispatcher\Event' => 'Symfony\Contracts\EventDispatcher\Event',
    ], 'symfony/event-dispatcher', '>=4.3');
    // symfony/framework-bundle 4.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // assets deprecation
        'Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper' => 'Symfony\Component\Asset\Packages',
        // templating
        'Symfony\Bundle\FrameworkBundle\Templating\EngineInterface' => 'Symfony\Component\Templating\EngineInterface',
    ], 'symfony/framework-bundle', '>=4.3');
    // symfony/http-foundation 4.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // MimeType
        'Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface' => 'Symfony\Component\Mime\MimeTypesInterface',
        'Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface' => 'Symfony\Component\Mime\MimeTypesInterface',
        'Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser' => 'Symfony\Component\Mime\MimeTypes',
        'Symfony\Component\HttpFoundation\File\MimeType\FileBinaryMimeTypeGuesser' => 'Symfony\Component\Mime\FileBinaryMimeTypeGuesser',
        'Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser' => 'Symfony\Component\Mime\FileinfoMimeTypeGuesser',
    ], 'symfony/http-foundation', '>=4.3');
    // symfony/http-kernel 4.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // EventDispatcher
        'Symfony\Component\HttpKernel\Event\FilterControllerArgumentsEvent' => 'Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent',
        'Symfony\Component\HttpKernel\Event\FilterControllerEvent' => 'Symfony\Component\HttpKernel\Event\ControllerEvent',
        'Symfony\Component\HttpKernel\Event\FilterResponseEvent' => 'Symfony\Component\HttpKernel\Event\ResponseEvent',
        'Symfony\Component\HttpKernel\Event\GetResponseEvent' => 'Symfony\Component\HttpKernel\Event\RequestEvent',
        'Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent' => 'Symfony\Component\HttpKernel\Event\ViewEvent',
        'Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent' => 'Symfony\Component\HttpKernel\Event\ExceptionEvent',
        'Symfony\Component\HttpKernel\Event\PostResponseEvent' => 'Symfony\Component\HttpKernel\Event\TerminateEvent',
        // @todo unpack after YAML to PHP migration, Symfony\Component\HttpKernel\Client: Symfony\Component\HttpKernel\HttpKernelBrowser
        'Symfony\Component\HttpKernel\EventListener\TranslatorListener' => 'Symfony\Component\HttpKernel\EventListener\LocaleAwareListener',
    ], 'symfony/http-kernel', '>=4.3');
    // symfony/security-core 4.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        # Security
        'Symfony\Component\Security\Core\Encoder\Argon2iPasswordEncoder' => 'Symfony\Component\Security\Core\Encoder\SodiumPasswordEncoder',
        'Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder' => 'Symfony\Component\Security\Core\Encoder\NativePasswordEncoder',
    ], 'symfony/security-core', '>=4.3');
    // symfony/security-http 4.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\Security\Http\Firewall', 'handleRequest', 'callListeners')], 'symfony/security-http', '>=4.3');
    // symfony/workflow 4.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(ArgumentAdderRector::class, [new ArgumentAdder('Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface', 'setMarking', 2, 'context', [])], 'symfony/workflow', '>=4.3');
    // symfony/dependency-injection 4.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameFunctionRector::class, ['Symfony\Component\DependencyInjection\Loader\Configurator\tagged' => 'Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator'], 'symfony/dependency-injection', '>=4.4');
    // symfony/http-kernel 4.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // https://github.com/symfony/http-kernel/blob/801b925e308518ddf821ba91952c41ae77c77507/Event/GetResponseForExceptionEvent.php#L55
        new MethodCallRename('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent', 'getException', 'getThrowable'),
        // https://github.com/symfony/http-kernel/blob/801b925e308518ddf821ba91952c41ae77c77507/Event/GetResponseForExceptionEvent.php#L67
        new MethodCallRename('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent', 'setException', 'setThrowable'),
    ], 'symfony/http-kernel', '>=4.4');
    // symfony/templating 4.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\Templating\EngineInterface' => 'Twig\Environment'], 'symfony/templating', '>=4.4');
    // symfony/console 5.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\Console\Application', 'renderException', 'renderThrowable'), new MethodCallRename('Symfony\Component\Console\Application', 'doRenderException', 'doRenderThrowable')], 'symfony/console', '>=5.0');
    // symfony/console 5.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'setCatchExceptions', 0, new BooleanType()), new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'setAutoExit', 0, new BooleanType()), new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'setName', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'setVersion', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'register', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'get', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'has', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'findNamespace', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'find', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'all', 0, $nullableStringType), new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'getAbbreviations', 0, $arrayType), new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'extractNamespace', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'extractNamespace', 1, $nullableIntegerType), new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'setDefaultCommand', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'setDefaultCommand', 1, new BooleanType()), new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'mergeApplicationDefinition', 0, new BooleanType()), new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'addArgument', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'addArgument', 1, $nullableIntegerType), new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'addArgument', 2, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'addOption', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'addOption', 2, $nullableIntegerType), new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'addOption', 3, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'setName', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'setProcessTitle', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'setHidden', 0, new BooleanType()), new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'setDescription', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'setHelp', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'setAliases', 0, $iterableType), new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'getSynopsis', 0, new BooleanType()), new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'addUsage', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'getHelper', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\CommandLoader\CommandLoaderInterface', 'get', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\CommandLoader\CommandLoaderInterface', 'has', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Input\InputInterface', 'getArgument', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Input\InputInterface', 'setArgument', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Input\InputInterface', 'getOption', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Input\InputInterface', 'setOption', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Input\InputInterface', 'hasOption', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Console\Input\InputInterface', 'setInteractive', 0, new BooleanType()), new AddParamTypeDeclaration('Symfony\Component\Console\Output\OutputInterface', 'write', 1, new BooleanType()), new AddParamTypeDeclaration('Symfony\Component\Console\Output\OutputInterface', 'write', 2, new IntegerType()), new AddParamTypeDeclaration('Symfony\Component\Console\Output\OutputInterface', 'writeln', 1, new IntegerType()), new AddParamTypeDeclaration('Symfony\Component\Console\Output\OutputInterface', 'setVerbosity', 0, new IntegerType()), new AddParamTypeDeclaration('Symfony\Component\Console\Output\OutputInterface', 'setDecorated', 0, new BooleanType())], 'symfony/console', '>=5.0');
    // symfony/debug 5.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\Debug\Debug' => 'Symfony\Component\ErrorHandler\Debug'], 'symfony/debug', '>=5.0');
    // symfony/doctrine-bridge 5.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Symfony\Bridge\Doctrine\Security\User\EntityUserProvider', 'loadUserByUsername', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Bridge\Doctrine\Security\User\EntityUserProvider', 'supportsClass', 0, new StringType())], 'symfony/doctrine-bridge', '>=5.0');
    // symfony/event-dispatcher 5.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddParamTypeDeclarationRector::class, [
        // @see https://github.com/symfony/symfony/issues/32179
        new AddParamTypeDeclaration('Symfony\Component\EventDispatcher\EventDispatcherInterface', 'addListener', 0, new StringType()),
        new AddParamTypeDeclaration('Symfony\Component\EventDispatcher\EventDispatcherInterface', 'addListener', 2, new IntegerType()),
        new AddParamTypeDeclaration('Symfony\Component\EventDispatcher\EventDispatcherInterface', 'removeListener', 0, new StringType()),
        new AddParamTypeDeclaration('Symfony\Component\EventDispatcher\EventDispatcherInterface', 'getListeners', 0, $nullableStringType),
        new AddParamTypeDeclaration('Symfony\Component\EventDispatcher\EventDispatcherInterface', 'getListenerPriority', 0, new StringType()),
        new AddParamTypeDeclaration('Symfony\Component\EventDispatcher\EventDispatcherInterface', 'hasListeners', 0, $nullableStringType),
        new AddParamTypeDeclaration('Symfony\Component\EventDispatcher\EventDispatcher', 'dispatch', 0, new ObjectWithoutClassType()),
    ], 'symfony/event-dispatcher', '>=5.0');
    // symfony/form 5.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Symfony\Component\Form\AbstractExtension', 'getType', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\AbstractExtension', 'hasType', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\AbstractExtension', 'getTypeExtensions', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\AbstractExtension', 'hasTypeExtensions', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\Form', 'add', 1, $nullableStringType), new AddParamTypeDeclaration('Symfony\Component\Form\Form', 'remove', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\Form', 'has', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\Form', 'get', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\FormBuilderInterface', 'add', 1, $nullableStringType), new AddParamTypeDeclaration('Symfony\Component\Form\FormBuilderInterface', 'create', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\FormBuilderInterface', 'create', 1, $nullableStringType), new AddParamTypeDeclaration('Symfony\Component\Form\FormBuilderInterface', 'get', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\FormBuilderInterface', 'remove', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\FormBuilderInterface', 'has', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\FormExtensionInterface', 'getTypeExtensions', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\FormExtensionInterface', 'hasTypeExtensions', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'create', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'createNamed', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'createNamed', 1, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'createForProperty', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'createForProperty', 1, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'createBuilder', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'createNamedBuilder', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'createNamedBuilder', 1, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'createBuilderForProperty', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'createBuilderForProperty', 1, new StringType())], 'symfony/form', '>=5.0');
    // symfony/form 5.0, changed back in 5.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Symfony\Component\Form\DataMapperInterface', 'mapFormsToData', 0, $iterableType), new AddParamTypeDeclaration('Symfony\Component\Form\DataMapperInterface', 'mapDataToForms', 1, $iterableType)], 'symfony/form', '>=5.0 <5.3');
    // symfony/process 5.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Symfony\Component\Process\Process', 'signal', 0, new IntegerType()), new AddParamTypeDeclaration('Symfony\Component\Process\Process', 'stop', 0, new FloatType()), new AddParamTypeDeclaration('Symfony\Component\Process\Process', 'stop', 1, $nullableIntegerType), new AddParamTypeDeclaration('Symfony\Component\Process\Process', 'setTty', 0, new BooleanType()), new AddParamTypeDeclaration('Symfony\Component\Process\Process', 'setPty', 0, new BooleanType()), new AddParamTypeDeclaration('Symfony\Component\Process\Process', 'setWorkingDirectory', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Process\Process', 'inheritEnvironmentVariables', 0, new BooleanType()), new AddParamTypeDeclaration('Symfony\Component\Process\Process', 'updateStatus', 0, new BooleanType())], 'symfony/process', '>=5.0');
    // symfony/security-core 5.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Symfony\Component\Security\Core\User\UserProviderInterface', 'loadUserByUsername', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Component\Security\Core\User\UserProviderInterface', 'supportsClass', 0, new StringType())], 'symfony/security-core', '>=5.0');
    // symfony/translation-contracts 5.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Symfony\Contracts\Translation\TranslatorInterface', 'setLocale', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Contracts\Translation\TranslatorInterface', 'trans', 0, new StringType()), new AddParamTypeDeclaration('Symfony\Contracts\Translation\TranslatorInterface', 'trans', 2, new UnionType([new NullType(), new StringType()])), new AddParamTypeDeclaration('Symfony\Contracts\Translation\TranslatorInterface', 'trans', 3, new UnionType([new NullType(), new StringType()]))], 'symfony/translation-contracts', '>=5.0');
    // symfony/config 5.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\Config\Definition\BaseNode', 'getDeprecationMessage', 'getDeprecation')], 'symfony/config', '>=5.1');
    // symfony/dependency-injection 5.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameFunctionRector::class, ['Symfony\Component\DependencyInjection\Loader\Configurator\inline' => 'Symfony\Component\DependencyInjection\Loader\Configurator\inline_service', 'Symfony\Component\DependencyInjection\Loader\Configurator\ref' => 'Symfony\Component\DependencyInjection\Loader\Configurator\service'], 'symfony/dependency-injection', '>=5.1');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\DependencyInjection\Definition', 'getDeprecationMessage', 'getDeprecation'), new MethodCallRename('Symfony\Component\DependencyInjection\Alias', 'getDeprecationMessage', 'getDeprecation')], 'symfony/dependency-injection', '>=5.1');
    // symfony/event-dispatcher 5.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy' => 'Symfony\Component\EventDispatcher\EventDispatcherInterface'], 'symfony/event-dispatcher', '>=5.1');
    // symfony/form 5.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer', 'ROUND_FLOOR', 'NumberFormatter', 'ROUND_FLOOR'), new RenameClassAndConstFetch('Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer', 'ROUND_DOWN', 'NumberFormatter', 'ROUND_DOWN'), new RenameClassAndConstFetch('Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer', 'ROUND_HALF_DOWN', 'NumberFormatter', 'ROUND_HALFDOWN'), new RenameClassAndConstFetch('Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer', 'ROUND_HALF_EVEN', 'NumberFormatter', 'ROUND_HALFEVEN'), new RenameClassAndConstFetch('Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer', 'ROUND_HALFUP', 'NumberFormatter', 'ROUND_HALFUP'), new RenameClassAndConstFetch('Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer', 'ROUND_UP', 'NumberFormatter', 'ROUND_UP'), new RenameClassAndConstFetch('Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer', 'ROUND_CEILING', 'NumberFormatter', 'ROUND_CEILING')], 'symfony/form', '>=5.1');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\Form\Extension\Validator\Util\ServerParams' => 'Symfony\Component\Form\Util\ServerParams'], 'symfony/form', '>=5.1');
    // symfony/framework-bundle 5.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait', 'configureRoutes', 0, new ObjectType('Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator'))], 'symfony/framework-bundle', '>=5.1');
    // symfony/http-foundation 5.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(ReplaceArgumentDefaultValueRector::class, [new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', 'withSameSite', 0, 'none', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_NONE'), new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', 'withSameSite', 0, 'lax', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_LAX'), new ReplaceArgumentDefaultValue('Symfony\Component\HttpFoundation\Cookie', 'withSameSite', 0, 'strict', 'Symfony\Component\HttpFoundation\Cookie::SAMESITE_STRICT')], 'symfony/http-foundation', '>=5.1');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(StaticCallToNewRector::class, [new StaticCallToNew('Symfony\Component\HttpFoundation\Response', 'create'), new StaticCallToNew('Symfony\Component\HttpFoundation\JsonResponse', 'create'), new StaticCallToNew('Symfony\Component\HttpFoundation\RedirectResponse', 'create'), new StaticCallToNew('Symfony\Component\HttpFoundation\StreamedResponse', 'create')], 'symfony/http-foundation', '>=5.1');
    // symfony/inflector 5.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/35092
        'Symfony\Component\Inflector' => 'Symfony\Component\String\Inflector\InflectorInterface',
    ], 'symfony/inflector', '>=5.1');
    // symfony/notifier 5.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/35828
        new MethodCallRename('Symfony\Component\Notifier\Bridge\Slack\Slack', 'channel', 'recipient'),
    ], 'symfony/notifier', '>=5.1');
    // symfony/security-core 5.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameStringRector::class, [
        // @see https://github.com/symfony/symfony/pull/35858
        'ROLE_PREVIOUS_ADMIN' => 'IS_IMPERSONATOR',
    ], 'symfony/security-core', '>=5.1');
    // symfony/mime 5.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#mime
        new MethodCallRename('Symfony\Component\Mime\Address', 'fromString', 'create'),
    ], 'symfony/mime', '>=5.2');
    // symfony/notifier 5.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Symfony\Component\Notifier\NotifierInterface', 'send', 1, new ObjectType('Symfony\Component\Notifier\Recipient\RecipientInterface')), new AddParamTypeDeclaration('Symfony\Component\Notifier\Notifier', 'getChannels', 1, new ObjectType('Symfony\Component\Notifier\Recipient\RecipientInterface')), new AddParamTypeDeclaration('Symfony\Component\Notifier\Channel\ChannelInterface', 'notify', 1, new ObjectType('Symfony\Component\Notifier\Recipient\RecipientInterface')), new AddParamTypeDeclaration('Symfony\Component\Notifier\Channel\ChannelInterface', 'supports', 1, new ObjectType('Symfony\Component\Notifier\Recipient\RecipientInterface')), new AddParamTypeDeclaration('Symfony\Component\Notifier\Notification\ChatNotificationInterface', 'asChatMessage', 0, new ObjectType('Symfony\Component\Notifier\Recipient\RecipientInterface')), new AddParamTypeDeclaration('Symfony\Component\Notifier\Notification\EmailNotificationInterface', 'asEmailMessage', 0, new ObjectType('Symfony\Component\Notifier\Recipient\EmailRecipientInterface')), new AddParamTypeDeclaration('Symfony\Component\Notifier\Notification\SmsNotificationInterface', 'asSmsMessage', 0, new ObjectType('Symfony\Component\Notifier\Recipient\SmsRecipientInterface'))], 'symfony/notifier', '>=5.2');
    // symfony/security-core 5.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken', 'setProviderKey', 'setFirewallName'), new MethodCallRename('Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken', 'getProviderKey', 'getFirewallName'), new MethodCallRename('Symfony\Component\Security\Core\Authentication\Token\RememberMeToken', 'setProviderKey', 'setFirewallName'), new MethodCallRename('Symfony\Component\Security\Core\Authentication\Token\RememberMeToken', 'getProviderKey', 'getFirewallName'), new MethodCallRename('Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken', 'setProviderKey', 'setFirewallName'), new MethodCallRename('Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken', 'getProviderKey', 'getFirewallName'), new MethodCallRename('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken', 'setProviderKey', 'setFirewallName'), new MethodCallRename('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken', 'getProviderKey', 'getFirewallName')], 'symfony/security-core', '>=5.2');
    // symfony/security-http 5.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Symfony\Component\Security\Http\Firewall\AccessListener', 'PUBLIC_ACCESS', 'Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter', 'PUBLIC_ACCESS')], 'symfony/security-http', '>=5.2');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler', 'setProviderKey', 'setFirewallName'), new MethodCallRename('Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler', 'getProviderKey', 'getFirewallName')], 'symfony/security-http', '>=5.2');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenamePropertyRector::class, [new RenameProperty('Symfony\Component\Security\Http\RememberMe\AbstractRememberMeServices', 'providerKey', 'firewallName')], 'symfony/security-http', '>=5.2');
    // symfony/validator 5.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AttributeKeyToClassConstFetchRector::class, [new AttributeKeyToClassConstFetch('Symfony\Component\Validator\Constraints\Email', 'mode', 'Symfony\Component\Validator\Constraints\Email', ['strict' => 'VALIDATION_MODE_STRICT', 'loose' => 'VALIDATION_MODE_LOOSE', 'html5' => 'VALIDATION_MODE_HTML5'])], 'symfony/validator', '>=5.2');
    // symfony/console 5.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\Console\Helper\Helper', 'strlen', 'width'), new MethodCallRename('Symfony\Component\Console\Helper\Helper', 'strlenWithoutDecoration', 'removeDecoration')], 'symfony/console', '>=5.3');
    // symfony/form 5.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddParamTypeDeclarationRector::class, [
        // @see https://github.com/symfony/symfony/commit/ce77be2507631cd12e4ca37510dab37f4c2b759a
        new AddParamTypeDeclaration('Symfony\Component\Form\DataMapperInterface', 'mapFormsToData', 0, new ObjectType(\Traversable::class)),
        // @see https://github.com/symfony/symfony/commit/ce77be2507631cd12e4ca37510dab37f4c2b759a
        new AddParamTypeDeclaration('Symfony\Component\Form\DataMapperInterface', 'mapDataToForms', 1, new ObjectType(\Traversable::class)),
    ], 'symfony/form', '>=5.3');
    // symfony/http-foundation 5.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/40536
        new MethodCallRename('Symfony\Component\HttpFoundation\RequestStack', 'getMasterRequest', 'getMainRequest'),
    ], 'symfony/http-foundation', '>=5.3');
    // symfony/http-kernel 5.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassConstFetchRector::class, [
        // @see https://github.com/symfony/symfony/pull/40536
        new RenameClassConstFetch('Symfony\Component\HttpKernel\HttpKernelInterface', 'MASTER_REQUEST', 'MAIN_REQUEST'),
    ], 'symfony/http-kernel', '>=5.3');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\HttpKernel\Event\KernelEvent', 'isMasterRequest', 'isMainRequest')], 'symfony/http-kernel', '>=5.3');
    // symfony/security-core 5.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        'Symfony\Component\Security\Core\Exception\UsernameNotFoundException' => 'Symfony\Component\Security\Core\Exception\UserNotFoundException',
        // @see https://github.com/symfony/symfony/pull/39802
        'Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface' => 'Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface',
        'Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder' => 'Symfony\Component\PasswordHasher\Hasher\MessageDigestPasswordHasher',
        'Symfony\Component\Security\Core\Encoder\MigratingPasswordEncoder' => 'Symfony\Component\PasswordHasher\Hasher\MigratingPasswordHasher',
        'Symfony\Component\Security\Core\Encoder\NativePasswordEncoder' => 'Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher',
        'Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface' => 'Symfony\Component\PasswordHasher\PasswordHasherInterface',
        'Symfony\Component\Security\Core\Encoder\Pbkdf2PasswordEncoder' => 'Symfony\Component\PasswordHasher\Hasher\Pbkdf2PasswordHasher',
        'Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder' => 'Symfony\Component\PasswordHasher\Hasher\PlaintextPasswordHasher',
        'Symfony\Component\Security\Core\Encoder\SelfSaltingEncoderInterface' => 'Symfony\Component\PasswordHasher\LegacyPasswordHasherInterface',
        'Symfony\Component\Security\Core\Encoder\SodiumPasswordEncoder' => 'Symfony\Component\PasswordHasher\Hasher\SodiumPasswordHasher',
        'Symfony\Component\Security\Core\Encoder\UserPasswordEncoder' => 'Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher',
        'Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface' => 'Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface',
    ], 'symfony/security-core', '>=5.3');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\Security\Core\Authentication\Token\TokenInterface', 'getUsername', 'getUserIdentifier'), new MethodCallRename('Symfony\Component\Security\Core\Exception\UsernameNotFoundException', 'getUsername', 'getUserIdentifier'), new MethodCallRename('Symfony\Component\Security\Core\Exception\UsernameNotFoundException', 'setUsername', 'setUserIdentifier'), new MethodCallRename('Symfony\Component\Security\Core\Authentication\RememberMe\PersistentTokenInterface', 'getUsername', 'getUserIdentifier')], 'symfony/security-core', '>=5.3');
    // symfony/security-mailer 5.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\Mailer\Transport\AbstractTransportFactory', 'getEndpoint', new StringType())], 'symfony/security-mailer', '>=5.3');
    // symfony/cache 5.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/42965
        'Symfony\Component\Cache\Adapter\DoctrineAdapter' => 'Doctrine\Common\Cache\Psr6\CacheAdapter',
    ], 'symfony/cache', '>=5.4');
    // symfony/http-kernel 5.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/45615
        'Symfony\Component\HttpKernel\EventListener\AbstractTestSessionListener' => 'Symfony\Component\HttpKernel\EventListener\AbstractSessionListener',
        'Symfony\Component\HttpKernel\EventListener\TestSessionListener' => 'Symfony\Component\HttpKernel\EventListener\SessionListener',
    ], 'symfony/http-kernel', '>=5.4');
    // symfony/notifier 5.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/44271
        'Symfony\Component\Notifier\Bridge\Nexmo\NexmoTransportFactory' => 'Symfony\Component\Notifier\Bridge\Vonage\VonageTransportFactory',
        'Symfony\Component\Notifier\Bridge\Nexmo\NexmoTransport' => 'Symfony\Component\Notifier\Bridge\Vonage\VonageTransport',
    ], 'symfony/notifier', '>=5.4');
    // symfony/security-bundle 5.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/42582
        new MethodCallRename('Symfony\Bundle\SecurityBundle\Security\FirewallConfig', 'getListeners', 'getAuthenticators'),
        // @see https://github.com/symfony/symfony/pull/41754
        new MethodCallRename('Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension', 'addSecurityListenerFactory', 'addAuthenticatorFactory'),
    ], 'symfony/security-bundle', '>=5.4');
    // symfony/security-core 5.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassConstFetchRector::class, [
        new RenameClassAndConstFetch('Symfony\Component\Security\Core\AuthenticationEvents', 'AUTHENTICATION_SUCCESS', 'Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent', 'class'),
        new RenameClassAndConstFetch('Symfony\Component\Security\Core\AuthenticationEvents', 'AUTHENTICATION_FAILURE', 'Symfony\Component\Security\Core\Event\AuthenticationFailureEvent', 'class'),
        // @see https://github.com/symfony/symfony/pull/42510
        new RenameClassConstFetch('Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter', 'IS_ANONYMOUS', 'PUBLIC_ACCESS'),
        new RenameClassConstFetch('Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter', 'IS_AUTHENTICATED_ANONYMOUSLY', 'PUBLIC_ACCESS'),
    ], 'symfony/security-core', '>=5.4');
    // symfony/security-http 5.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/42050
        'Symfony\Component\Security\Http\Event\DeauthenticatedEvent' => 'Symfony\Component\Security\Http\Event\TokenDeauthenticatedEvent',
    ], 'symfony/security-http', '>=5.4');
    // symfony/validator 5.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AnnotationToAttributeRector::class, [new AnnotationToAttribute('Symfony\Component\Validator\Constraints\All'), new AnnotationToAttribute('Symfony\Component\Validator\Constraints\Collection'), new AnnotationToAttribute('Symfony\Component\Validator\Constraints\AtLeastOneOf'), new AnnotationToAttribute('Symfony\Component\Validator\Constraints\Sequentially')], 'symfony/validator', '>=5.4');
    // symfony/browser-kit 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\BrowserKit\AbstractBrowser', 'doRequestInProcess', new ObjectWithoutClassType()), new AddReturnTypeDeclaration('Symfony\Component\BrowserKit\AbstractBrowser', 'doRequest', new ObjectWithoutClassType()), new AddReturnTypeDeclaration('Symfony\Component\BrowserKit\AbstractBrowser', 'filterRequest', new ObjectWithoutClassType()), new AddReturnTypeDeclaration('Symfony\Component\BrowserKit\AbstractBrowser', 'filterResponse', $browserKitResponseType)], 'symfony/browser-kit', '>=6.0');
    // symfony/config 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Symfony\Component\Config\Loader\LoaderInterface', 'load', 0, new MixedType(\true)), new AddParamTypeDeclaration('Symfony\Component\Config\Loader\LoaderInterface', 'supports', 0, new MixedType(\true))], 'symfony/config', '>=6.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\Config\Loader\LoaderInterface', 'load', new MixedType()), new AddReturnTypeDeclaration('Symfony\Component\Config\Loader\Loader', 'import', new MixedType()), new AddReturnTypeDeclaration('Symfony\Component\Config\Definition\ConfigurationInterface', 'getConfigTreeBuilder', new ObjectType('Symfony\Component\Config\Definition\Builder\TreeBuilder')), new AddReturnTypeDeclaration('Symfony\Component\Config\FileLocator', 'locate', new UnionType([new StringType(), $arrayType])), new AddReturnTypeDeclaration('Symfony\Component\Config\FileLocatorInterface', 'locate', new UnionType([new StringType(), $arrayType])), new AddReturnTypeDeclaration('Symfony\Component\Config\Loader\FileLoader', 'import', new MixedType()), new AddReturnTypeDeclaration('Symfony\Component\Config\Loader\Loader', 'import', new MixedType()), new AddReturnTypeDeclaration('Symfony\Component\Config\Loader\LoaderInterface', 'load', new MixedType()), new AddReturnTypeDeclaration('Symfony\Component\Config\Loader\LoaderInterface', 'supports', new BooleanType()), new AddReturnTypeDeclaration('Symfony\Component\Config\Loader\LoaderInterface', 'getResolver', new ObjectType('Symfony\Component\Config\Loader\LoaderResolverInterface')), new AddReturnTypeDeclaration('Symfony\Component\Config\ResourceCheckerInterface', 'supports', new BooleanType()), new AddReturnTypeDeclaration('Symfony\Component\Config\ResourceCheckerInterface', 'isFresh', new BooleanType())], 'symfony/config', '>=6.0');
    // symfony/console 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [
        // @see https://github.com/symfony/symfony/pull/43028/files
        new AddReturnTypeDeclaration('Symfony\Component\Console\Helper\HelperInterface', 'getName', new StringType()),
        new AddReturnTypeDeclaration('Symfony\Component\Console\Application', 'doRun', new IntegerType()),
        new AddReturnTypeDeclaration('Symfony\Component\Console\Application', 'getLongVersion', new StringType()),
        new AddReturnTypeDeclaration('Symfony\Component\Console\Application', 'add', new UnionType([new NullType(), $commandType])),
        new AddReturnTypeDeclaration('Symfony\Component\Console\Application', 'get', $commandType),
        new AddReturnTypeDeclaration('Symfony\Component\Console\Application', 'find', $commandType),
        new AddReturnTypeDeclaration('Symfony\Component\Console\Application', 'all', $arrayType),
        new AddReturnTypeDeclaration('Symfony\Component\Console\Application', 'doRunCommand', new IntegerType()),
        new AddReturnTypeDeclaration('Symfony\Component\Console\Command\Command', 'isEnabled', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\Component\Console\Command\Command', 'execute', new IntegerType()),
        new AddReturnTypeDeclaration('Symfony\Component\Console\Helper\HelperInterface', 'getName', new StringType()),
        new AddReturnTypeDeclaration('Symfony\Component\Console\Input\InputInterface', 'getParameterOption', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\Component\Console\Input\InputInterface', 'getArgument', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\Component\Console\Input\InputInterface', 'getOption', new MixedType()),
    ], 'symfony/console', '>=6.0');
    // symfony/contracts 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/39484
        'Symfony\Contracts\HttpClient\HttpClientInterface\RemoteJsonManifestVersionStrategy' => 'Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy',
    ], 'symfony/contracts', '>=6.0');
    // symfony/dependency-injection 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass', 'processValue', new MixedType()), new AddReturnTypeDeclaration('Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface', 'getConfiguration', new UnionType([new NullType(), $configurationType])), new AddReturnTypeDeclaration('Symfony\Component\DependencyInjection\Extension\Extension', 'getXsdValidationBasePath', new UnionType([new StringType(), new ConstantBooleanType(\false)])), new AddReturnTypeDeclaration('Symfony\Component\DependencyInjection\Extension\Extension', 'getNamespace', new StringType()), new AddReturnTypeDeclaration('Symfony\Component\DependencyInjection\Extension\Extension', 'getConfiguration', new UnionType([new NullType(), $configurationType])), new AddReturnTypeDeclaration('Symfony\Component\DependencyInjection\Extension\ExtensionInterface', 'getNamespace', new StringType()), new AddReturnTypeDeclaration('Symfony\Component\DependencyInjection\Extension\ExtensionInterface', 'getXsdValidationBasePath', new UnionType([new StringType(), new ConstantBooleanType(\false)])), new AddReturnTypeDeclaration('Symfony\Component\DependencyInjection\Extension\ExtensionInterface', 'getAlias', new StringType()), new AddReturnTypeDeclaration('Symfony\Component\DependencyInjection\LazyProxy\Instantiator\InstantiatorInterface', 'instantiateProxy', new ObjectWithoutClassType()), new AddReturnTypeDeclaration('Symfony\Component\DependencyInjection\Container', 'getParameter', new UnionType($scalarTypes)), new AddReturnTypeDeclaration('Symfony\Component\DependencyInjection\ContainerInterface', 'getParameter', new UnionType($scalarTypes))], 'symfony/dependency-injection', '>=6.0');
    // symfony/doctrine-bridge 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/40403
        new MethodCallRename('Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface', 'loadUserByUsername', 'loadUserByIdentifier'),
    ], 'symfony/doctrine-bridge', '>=6.0');
    // symfony/event-dispatcher 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\EventDispatcher\EventSubscriberInterface', 'getSubscribedEvents', $arrayType)], 'symfony/event-dispatcher', '>=6.0');
    // symfony/expression-language 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface', 'getFunctions', $arrayType)], 'symfony/expression-language', '>=6.0');
    // symfony/form 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\Form\AbstractExtension', 'loadTypes', $arrayType), new AddReturnTypeDeclaration('Symfony\Component\Form\AbstractExtension', 'loadTypeGuesser', new UnionType([new NullType(), new ObjectType('Symfony\Component\Form\FormTypeGuesserInterface')])), new AddReturnTypeDeclaration('Symfony\Component\Form\AbstractRendererEngine', 'loadResourceForBlockName', new BooleanType()), new AddReturnTypeDeclaration('Symfony\Component\Form\AbstractType', 'getBlockPrefix', new StringType()), new AddReturnTypeDeclaration('Symfony\Component\Form\AbstractType', 'getParent', $nullableStringType), new AddReturnTypeDeclaration('Symfony\Component\Form\DataTransformerInterface', 'transform', new MixedType()), new AddReturnTypeDeclaration('Symfony\Component\Form\DataTransformerInterface', 'reverseTransform', new MixedType()), new AddReturnTypeDeclaration('Symfony\Component\Form\FormRendererEngineInterface', 'renderBlock', new StringType()), new AddReturnTypeDeclaration('Symfony\Component\Form\FormTypeGuesserInterface', 'guessType', new UnionType([new NullType(), $typeGuessType])), new AddReturnTypeDeclaration('Symfony\Component\Form\FormTypeGuesserInterface', 'guessRequired', $nullableValueGuessType), new AddReturnTypeDeclaration('Symfony\Component\Form\FormTypeGuesserInterface', 'guessMaxLength', $nullableValueGuessType), new AddReturnTypeDeclaration('Symfony\Component\Form\FormTypeGuesserInterface', 'guessPattern', $nullableValueGuessType), new AddReturnTypeDeclaration('Symfony\Component\Form\FormTypeInterface', 'getBlockPrefix', new StringType()), new AddReturnTypeDeclaration('Symfony\Component\Form\FormTypeInterface', 'getParent', $nullableStringType), new AddReturnTypeDeclaration('Symfony\Component\Form\FormTypeInterface', 'buildForm', new VoidType()), new AddReturnTypeDeclaration('Symfony\Component\Form\FormTypeInterface', 'configureOptions', new VoidType())], 'symfony/form', '>=6.0');
    // symfony/framework-bundle 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait', 'configureRoutes', 0, new ObjectType('Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator'))], 'symfony/framework-bundle', '>=6.0');
    // symfony/http-kernel 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\HttpKernel\KernelInterface', 'registerBundles', $iterableType), new AddReturnTypeDeclaration('Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface', 'isOptional', new BooleanType()), new AddReturnTypeDeclaration('Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface', 'warmUp', $arrayType), new AddReturnTypeDeclaration('Symfony\Component\HttpKernel\DataCollector\DataCollector', 'getCasters', $arrayType), new AddReturnTypeDeclaration('Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface', 'getName', new StringType()), new AddReturnTypeDeclaration('Symfony\Component\HttpKernel\HttpCache\HttpCache', 'forward', $httpFoundationResponseType), new AddReturnTypeDeclaration('Symfony\Component\HttpKernel\HttpKernelBrowser', 'doRequest', $httpFoundationResponseType), new AddReturnTypeDeclaration('Symfony\Component\HttpKernel\HttpKernelBrowser', 'getScript', new StringType()), new AddReturnTypeDeclaration('Symfony\Component\HttpKernel\Log\DebugLoggerInterface', 'getLogs', $arrayType), new AddReturnTypeDeclaration('Symfony\Component\HttpKernel\Log\DebugLoggerInterface', 'countErrors', new IntegerType())], 'symfony/http-kernel', '>=6.0');
    // symfony/options-resolver 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\OptionsResolver\OptionsResolver', 'setNormalizer', new SimpleStaticType('Symfony\Component\OptionsResolver\OptionsResolver')), new AddReturnTypeDeclaration('Symfony\Component\OptionsResolver\OptionsResolver', 'setAllowedValues', new SimpleStaticType('Symfony\Component\OptionsResolver\OptionsResolver')), new AddReturnTypeDeclaration('Symfony\Component\OptionsResolver\OptionsResolver', 'addAllowedValues', new SimpleStaticType('Symfony\Component\OptionsResolver\OptionsResolver')), new AddReturnTypeDeclaration('Symfony\Component\OptionsResolver\OptionsResolver', 'setAllowedTypes', new SimpleStaticType('Symfony\Component\OptionsResolver\OptionsResolver')), new AddReturnTypeDeclaration('Symfony\Component\OptionsResolver\OptionsResolver', 'addAllowedTypes', new SimpleStaticType('Symfony\Component\OptionsResolver\OptionsResolver'))], 'symfony/options-resolver', '>=6.0');
    // symfony/property-access 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\PropertyAccess\PropertyPathInterface', 'getLength', new IntegerType()), new AddReturnTypeDeclaration('Symfony\Component\PropertyAccess\PropertyPathInterface', 'getParent', new UnionType([new NullType(), new ObjectType('Symfony\Component\PropertyAccess\PropertyPathInterface')])), new AddReturnTypeDeclaration('Symfony\Component\PropertyAccess\PropertyPathInterface', 'getElements', $arrayType), new AddReturnTypeDeclaration('Symfony\Component\PropertyAccess\PropertyPathInterface', 'getElement', new StringType()), new AddReturnTypeDeclaration('Symfony\Component\PropertyAccess\PropertyPathInterface', 'isProperty', new BooleanType()), new AddReturnTypeDeclaration('Symfony\Component\PropertyAccess\PropertyPathInterface', 'isIndex', new BooleanType())], 'symfony/property-access', '>=6.0');
    // symfony/property-info 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\PropertyInfo\PropertyAccessExtractorInterface', 'isReadable', $nullableBooleanType), new AddReturnTypeDeclaration('Symfony\Component\PropertyInfo\PropertyAccessExtractorInterface', 'isWritable', $nullableBooleanType), new AddReturnTypeDeclaration('Symfony\Component\PropertyInfo\PropertyListExtractorInterface', 'getProperties', $nullableArrayType), new AddReturnTypeDeclaration('Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface', 'getTypes', $nullableArrayType)], 'symfony/property-info', '>=6.0');
    // symfony/routing 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\Routing\Loader\AnnotationClassLoader', 'getDefaultRouteName', new StringType()), new AddReturnTypeDeclaration('Symfony\Component\Routing\Router', 'getRouteCollection', $routeCollectionType), new AddReturnTypeDeclaration('Symfony\Component\Routing\RouterInterface', 'getRouteCollection', $routeCollectionType)], 'symfony/routing', '>=6.0');
    // symfony/security-core 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [
        // @see https://wouterj.nl/2021/09/symfony-6-native-typing#when-upgrading-to-symfony-54
        new AddReturnTypeDeclaration('Symfony\Component\Security\Core\User\UserInterface', 'getRoles', new ArrayType(new MixedType(), new MixedType())),
        new AddReturnTypeDeclaration('Symfony\Component\Security\Core\Authentication\RememberMe\TokenProviderInterface', 'loadTokenBySeries', new ObjectType('Symfony\Component\Security\Core\Authentication\RememberMe\PersistentTokenInterface')),
        new AddReturnTypeDeclaration('Symfony\Component\Security\Core\Authorization\Voter\VoterInterface', 'vote', new IntegerType()),
        new AddReturnTypeDeclaration('Symfony\Component\Security\Core\Exception\AuthenticationException', 'getMessageKey', new StringType()),
        new AddReturnTypeDeclaration('Symfony\Component\Security\Core\User\UserProviderInterface', 'refreshUser', new ObjectType('Symfony\Component\Security\Core\User\UserInterface')),
        new AddReturnTypeDeclaration('Symfony\Component\Security\Core\User\UserProviderInterface', 'supportsClass', new BooleanType()),
    ], 'symfony/security-core', '>=6.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        new MethodCallRename('Symfony\Component\Security\Core\User\UserProviderInterface', 'loadUserByUsername', 'loadUserByIdentifier'),
        // @see https://github.com/rectorphp/rector-symfony/issues/112
        new MethodCallRename('Symfony\Component\Security\Core\User\UserInterface', 'getUsername', 'getUserIdentifier'),
    ], 'symfony/security-core', '>=6.0');
    // symfony/security-http 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface', 'start', $httpFoundationResponseType), new AddReturnTypeDeclaration('Symfony\Component\Security\Http\Firewall', 'getSubscribedEvents', $arrayType), new AddReturnTypeDeclaration('Symfony\Component\Security\Http\FirewallMapInterface', 'getListeners', $arrayType), new AddReturnTypeDeclaration('Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface', 'authenticate', new ObjectType('Symfony\Component\Security\Http\Authenticator\Passport\Passport'))], 'symfony/security-http', '>=6.0');
    // symfony/serializer 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\Serializer\Encoder\DecoderInterface', 'decode', new MixedType()), new AddReturnTypeDeclaration('Symfony\Component\Serializer\Encoder\DecoderInterface', 'supportsDecoding', new BooleanType()), new AddReturnTypeDeclaration('Symfony\Component\Serializer\Normalizer\AbstractNormalizer', 'getAllowedAttributes', new UnionType([$arrayType, new BooleanType()])), new AddReturnTypeDeclaration('Symfony\Component\Serializer\Normalizer\AbstractNormalizer', 'isAllowedAttribute', new BooleanType()), new AddReturnTypeDeclaration('Symfony\Component\Serializer\Normalizer\AbstractNormalizer', 'instantiateObject', new ObjectWithoutClassType()), new AddReturnTypeDeclaration('Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer', 'supportsNormalization', new BooleanType()), new AddReturnTypeDeclaration('Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer', 'instantiateObject', new ObjectWithoutClassType()), new AddReturnTypeDeclaration('Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer', 'extractAttributes', $arrayType), new AddReturnTypeDeclaration('Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer', 'getAttributeValue', new MixedType()), new AddReturnTypeDeclaration('Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer', 'supportsDenormalization', new BooleanType()), new AddReturnTypeDeclaration('Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer', 'denormalize', new MixedType()), new AddReturnTypeDeclaration('Symfony\Component\Serializer\Normalizer\DenormalizerInterface', 'denormalize', new MixedType()), new AddReturnTypeDeclaration('Symfony\Component\Serializer\Normalizer\DenormalizerInterface', 'supportsDenormalization', new BooleanType()), new AddReturnTypeDeclaration('Symfony\Component\Serializer\Normalizer\NormalizerInterface', 'supportsNormalization', new BooleanType()), new AddReturnTypeDeclaration('Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer', 'normalize', $scalarArrayObjectUnionType), new AddReturnTypeDeclaration('Symfony\Component\Serializer\Normalizer\NormalizerInterface', 'normalize', $scalarArrayObjectUnionType)], 'symfony/serializer', '>=6.0');
    // symfony/templating 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\Templating\Helper\HelperInterface', 'getName', new StringType())], 'symfony/templating', '>=6.0');
    // symfony/translation 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\Translation\Extractor\AbstractFileExtractor', 'canBeExtracted', new BooleanType()), new AddReturnTypeDeclaration('Symfony\Component\Translation\Extractor\AbstractFileExtractor', 'extractFromDirectory', $iterableType)], 'symfony/translation', '>=6.0');
    // symfony/validator 6.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\Validator\Constraint', 'getDefaultOption', $nullableStringType), new AddReturnTypeDeclaration('Symfony\Component\Validator\Constraint', 'getRequiredOptions', $arrayType), new AddReturnTypeDeclaration('Symfony\Component\Validator\Constraint', 'validatedBy', new StringType()), new AddReturnTypeDeclaration('Symfony\Component\Validator\Constraint', 'getTargets', new UnionType([new StringType(), $arrayType]))], 'symfony/validator', '>=6.0');
    // symfony/serializer 6.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/43982
        'Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface' => 'Symfony\Component\Serializer\Normalizer\DenormalizerInterface',
        'Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface' => 'Symfony\Component\Serializer\Normalizer\NormalizerInterface',
    ], 'symfony/serializer', '>=6.1');
    // symfony/validator 6.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AttributeKeyToClassConstFetchRector::class, [new AttributeKeyToClassConstFetch('Symfony\Component\Validator\Constraints\Email', 'mode', 'Symfony\Component\Validator\Constraints\Email', ['html5-allow-no-tld' => 'VALIDATION_MODE_HTML5_ALLOW_NO_TLD'])], 'symfony/validator', '>=6.1');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/45623
        'Symfony\Component\Validator\Constraints\ExpressionLanguageSyntax' => 'Symfony\Component\Validator\Constraints\ExpressionSyntax',
        'Symfony\Component\Validator\Constraints\ExpressionLanguageSyntaxValidator' => 'Symfony\Component\Validator\Constraints\ExpressionSyntaxValidator',
    ], 'symfony/validator', '>=6.1');
    // symfony/console 6.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [
        // @see https://github.com/symfony/symfony/pull/49347
        new AddReturnTypeDeclaration('Symfony\Component\Console\Command\Command', 'configure', new VoidType()),
    ], 'symfony/console', '>=6.2');
    // symfony/framework-bundle 6.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/46854
        new MethodCallRename('Symfony\Bundle\FrameworkBundle\Controller\AbstractController', 'renderForm', 'render'),
    ], 'symfony/framework-bundle', '>=6.2');
    // symfony/http-foundation 6.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/47595
        'Symfony\Component\HttpFoundation\ExpressionRequestMatcher' => 'Symfony\Component\HttpFoundation\RequestMatcher\ExpressionRequestMatcher',
        'Symfony\Component\HttpFoundation\RequestMatcher' => 'Symfony\Component\HttpFoundation\ChainRequestMatcher',
    ], 'symfony/http-foundation', '>=6.2');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/45034
        new MethodCallRename('Symfony\Component\HttpFoundation\Request', 'getContentType', 'getContentTypeFormat'),
    ], 'symfony/http-foundation', '>=6.2');
    // symfony/http-kernel 6.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/46880
        'Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache' => 'Symfony\Component\HttpKernel\Attribute\Cache',
        // @see https://github.com/symfony/symfony/pull/47363
        'Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface' => 'Symfony\Component\HttpKernel\Controller\ValueResolverInterface',
    ], 'symfony/http-kernel', '>=6.2');
    // symfony/mail-pace-mailer 6.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/46714
        'Symfony\Component\Mailer\Bridge\OhMySmtp\Transport\OhMySmtpApiTransport' => 'Symfony\Component\Mailer\Bridge\MailPace\Transport\MailPaceApiTransport',
        'Symfony\Component\Mailer\Bridge\OhMySmtp\Transport\OhMySmtpSmtpTransport' => 'Symfony\Component\Mailer\Bridge\MailPace\Transport\MailPaceSmtpTransport',
        'Symfony\Component\Mailer\Bridge\OhMySmtp\Transport\OhMySmtpTransportFactory' => 'Symfony\Component\Mailer\Bridge\MailPace\Transport\MailPaceTransportFactory',
    ], 'symfony/mail-pace-mailer', '>=6.2');
    // symfony/mime 6.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/47711
        new MethodCallRename('Symfony\Component\Mime\Email', 'attachPart', 'addPart'),
    ], 'symfony/mime', '>=6.2');
    // symfony/security-bundle 6.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/46094
        'Symfony\Component\Security\Core\Security' => 'Symfony\Bundle\SecurityBundle\Security',
    ], 'symfony/security-bundle', '>=6.2');
    // symfony/security-http 6.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Symfony\Component\Security\Core\Security', 'ACCESS_DENIED_ERROR', 'Symfony\Component\Security\Http\SecurityRequestAttributes', 'ACCESS_DENIED_ERROR'), new RenameClassAndConstFetch('Symfony\Component\Security\Core\Security', 'AUTHENTICATION_ERROR', 'Symfony\Component\Security\Http\SecurityRequestAttributes', 'AUTHENTICATION_ERROR'), new RenameClassAndConstFetch('Symfony\Component\Security\Core\Security', 'LAST_USERNAME', 'Symfony\Component\Security\Http\SecurityRequestAttributes', 'LAST_USERNAME'), new RenameClassAndConstFetch('Symfony\Component\Security\Core\Security', 'MAX_USERNAME_LENGTH', 'Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge', 'MAX_USERNAME_LENGTH')], 'symfony/security-http', '>=6.2');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/46907
        'Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted' => 'Symfony\Component\Security\Http\Attribute\IsGranted',
    ], 'symfony/security-http', '>=6.2');
    // symfony/symfony 6.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AnnotationToAttributeRector::class, [
        // @see https://github.com/symfony/symfony/pull/46907
        new AnnotationToAttribute('Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted'),
        // @see https://github.com/symfony/symfony/pull/46880
        new AnnotationToAttribute('Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache'),
        // @see https://github.com/symfony/symfony/pull/46906
        new AnnotationToAttribute('Sensio\Bundle\FrameworkExtraBundle\Configuration\Template'),
    ], 'symfony/symfony', '>=6.2');
    // symfony/translation 6.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/46161
        'Symfony\Component\Translation\Extractor\PhpAstExtractor' => 'Symfony\Component\Translation\Extractor\PhpAstExtractor',
    ], 'symfony/translation', '>=6.2');
    // symfony/twig-bridge 6.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/46906
        'Sensio\Bundle\FrameworkExtraBundle\Configuration\Template' => 'Symfony\Bridge\Twig\Attribute\Template',
    ], 'symfony/twig-bridge', '>=6.2');
    // symfony/validator 6.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassConstFetchRector::class, [new RenameClassConstFetch('Symfony\Component\Validator\Constraints\Email', 'VALIDATION_MODE_LOOSE', 'VALIDATION_MODE_HTML5')], 'symfony/validator', '>=6.2');
    // symfony/dependency-injection 6.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/commit/b653adf426aedc66d16c5fc1cf71e261f20b9638
        'Symfony\Component\DependencyInjection\Attribute\MapDecorated' => 'Symfony\Component\DependencyInjection\Attribute\AutowireDecorated',
    ], 'symfony/dependency-injection', '>=6.3');
    // symfony/http-client 6.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/commit/20ab567385e3812ef661dae01a1fdc5d1bde2666
        'Http\Client\HttpClient' => 'Psr\Http\Client\ClientInterface',
    ], 'symfony/http-client', '>=6.3');
    // symfony/messenger 6.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/commit/9415b438b75204c72ff66b838307b73646393cbf
        'Symfony\Component\Messenger\EventListener\StopWorkerOnSigtermSignalListener' => 'Symfony\Component\Messenger\EventListener\StopWorkerOnSignalsListener',
        // @see https://github.com/symfony/symfony/commit/a7926b2d83f35fe53c41a28d8055490cc1955928
        'Symfony\Component\Messenger\Transport\InMemoryTransport' => 'Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport',
        'Symfony\Component\Messenger\Transport\InMemoryTransportFactory' => 'Symfony\Component\Messenger\Transport\InMemory\InMemoryTransportFactory',
    ], 'symfony/messenger', '>=6.3');
    // symfony/error-handler 6.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\HttpKernel\Debug\FileLinkFormatter' => 'Symfony\Component\ErrorHandler\ErrorRenderer\FileLinkFormatter'], 'symfony/error-handler', '>=6.4');
    // symfony/form 6.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\Form\DataTransformerInterface', 'transform', new MixedType()), new AddReturnTypeDeclaration('Symfony\Component\Form\DataTransformerInterface', 'reverseTransform', new MixedType())], 'symfony/form', '>=6.4');
    // symfony/http-foundation 6.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\HttpKernel\UriSigner' => 'Symfony\Component\HttpFoundation\UriSigner'], 'symfony/http-foundation', '>=6.4');
    // symfony/routing 6.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameAttributeRector::class, [new RenameAttribute('Symfony\Component\Routing\Annotation\Route', 'Symfony\Component\Routing\Attribute\Route')], 'symfony/routing', '>=6.4');
    // symfony/contracts 7.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AnnotationToAttributeRector::class, [new AnnotationToAttribute('required', 'Symfony\Contracts\Service\Attribute\Required')], 'symfony/contracts', '>=7.0');
    // symfony/dependency-injection 7.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameAttributeRector::class, [new RenameAttribute('Symfony\Component\DependencyInjection\Attribute\MapDecorated', 'Symfony\Component\DependencyInjection\Attribute\AutowireDecorated')], 'symfony/dependency-injection', '>=7.0');
    // symfony/http-foundation 7.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/blob/7.0/UPGRADE-7.0.md#httpfoundation
        'Symfony\Component\HttpFoundation\RequestMatcher' => 'Symfony\Component\HttpFoundation\ChainRequestMatcher',
        'Symfony\Component\HttpFoundation\ExpressionRequestMatcher' => 'Symfony\Component\HttpFoundation\RequestMatcher\ExpressionRequestMatcher',
    ], 'symfony/http-foundation', '>=7.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\HttpFoundation\Request', 'getContentType', 'getContentTypeFormat')], 'symfony/http-foundation', '>=7.0');
    // symfony/routing 7.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameAttributeRector::class, [new RenameAttribute('Symfony\Component\Routing\Annotation\Route', 'Symfony\Component\Routing\Attribute\Route')], 'symfony/routing', '>=7.0');
    // symfony/serializer 7.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/blob/7.0/UPGRADE-7.0.md#frameworkbundle
        'Symfony\Component\Serializer\Normalizer\ObjectNormalizer' => 'Symfony\Component\Serializer\Normalizer\NormalizerInterface',
        'Symfony\Component\Serializer\Normalizer\PropertyNormalizer' => 'Symfony\Component\Serializer\Normalizer\NormalizerInterface',
    ], 'symfony/serializer', '>=7.0');
    // symfony/dependency-injection 7.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameAttributeRector::class, [new RenameAttribute('Symfony\Component\DependencyInjection\Attribute\TaggedIterator', 'Symfony\Component\DependencyInjection\Attribute\AutowireIterator'), new RenameAttribute('Symfony\Component\DependencyInjection\Attribute\TaggedLocator', 'Symfony\Component\DependencyInjection\Attribute\AutowireLocator')], 'symfony/dependency-injection', '>=7.1');
    // symfony/serializer 7.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // typo fix
        new MethodCallRename('Symfony\Component\Serializer\Context\Normalizer\AbstractNormalizerContextBuilder', 'withDefaultContructorArguments', 'withDefaultConstructorArguments'),
    ], 'symfony/serializer', '>=7.1');
    // symfony/mailer 7.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/blob/7.4/UPGRADE-7.2.md#mailer
        'Symfony\Component\Mailer\Test\TransportFactoryTestCase' => 'Symfony\Component\Mailer\Test\AbstractTransportFactoryTestCase',
    ], 'symfony/mailer', '>=7.2');
    // symfony/notifier 7.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/blob/7.4/UPGRADE-7.2.md#notifier
        'Symfony\Component\Mailer\Test\TransportFactoryTestCase' => 'Symfony\Component\Mailer\Test\AbstractTransportFactoryTestCase',
    ], 'symfony/notifier', '>=7.2');
    // symfony/serializer 7.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/blob/7.2/UPGRADE-7.2.md#serializer
        'Symfony\Component\Serializer\NameConverter\AdvancedNameConverterInterface' => 'Symfony\Component\Serializer\NameConverter\NameConverterInterface',
    ], 'symfony/serializer', '>=7.2');
    // symfony/translation 7.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\Translation\Test\ProviderFactoryTestCase' => 'Symfony\Component\Translation\Test\AbstractProviderFactoryTestCase'], 'symfony/translation', '>=7.2');
    // symfony/type-info 7.2
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/blob/7.4/UPGRADE-7.2.md#typeinfo
        new MethodCallRename('Symfony\Component\TypeInfo\Type', 'isA', 'isIdentifiedBy'),
        // @see https://github.com/symfony/symfony/blob/7.4/UPGRADE-7.2.md#typeinfo
        new MethodCallRename('Symfony\Component\TypeInfo\Type', 'is', 'isSatisfiedBy'),
    ], 'symfony/type-info', '>=7.2');
    // symfony/dependency-injection 7.3
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/blob/7.4/UPGRADE-7.3.md#dependencyinjection
        new MethodCallRename('Symfony\Component\DependencyInjection\ContainerBuilder', 'getAutoconfiguredAttributes', 'getAttributeAutoconfigurators'),
    ], 'symfony/dependency-injection', '>=7.3');
    // symfony/console 7.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\Console\Application', 'add', 'addCommand')], 'symfony/console', '>=7.4');
    // symfony/framework-bundle 7.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Bundle\FrameworkBundle\Console\Application', 'add', 'addCommand')], 'symfony/framework-bundle', '>=7.4');
    // symfony/json-streamer 7.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'getNativeToStreamValueTransformer', 'getValueTransformers'), new MethodCallRename('Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'getStreamToNativeValueTransformers', 'getValueTransformers'), new MethodCallRename('Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'withNativeToStreamValueTransformers', 'withValueTransformers'), new MethodCallRename('Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'withStreamToNativeValueTransformers', 'withValueTransformers'), new MethodCallRename('Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'withAdditionalNativeToStreamValueTransformer', 'withAdditionalValueTransformer'), new MethodCallRename('Symfony\Component\JsonStreamer\Mapping\PropertyMetadata', 'withAdditionalStreamToNativeValueTransformer', 'withAdditionalValueTransformer')], 'symfony/json-streamer', '>=7.4');
    // symfony/routing 7.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/61358
        new MethodCallRename('Symfony\Component\Routing\Attribute\Route', 'setEnv', 'setEnvs'),
    ], 'symfony/routing', '>=7.4');
    // symfony/workflow 7.4
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Bundle\FrameworkBundle\Command\WorkflowDumpCommand' => 'Symfony\Component\Workflow\Command\WorkflowDumpCommand'], 'symfony/workflow', '>=7.4');
    // symfony/dependency-injection 8.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Symfony\Component\HttpKernel\Bundle\BundleInterface' => 'Symfony\Component\DependencyInjection\Kernel\BundleInterface', 'Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass' => 'Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationPass', 'Symfony\Component\HttpKernel\Config\FileLocator' => 'Symfony\Component\DependencyInjection\Kernel\FileLocator', 'Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter' => 'Symfony\Component\DependencyInjection\ServicesResetter', 'Symfony\Component\HttpKernel\DependencyInjection\ServicesResetterInterface' => 'Symfony\Component\DependencyInjection\ServicesResetterInterface', 'Symfony\Component\HttpKernel\DependencyInjection\ResettableServicePass' => 'Symfony\Component\DependencyInjection\Compiler\ResettableServicePass', 'Symfony\Component\HttpKernel\DependencyInjection\Extension' => 'Symfony\Component\DependencyInjection\Extension\Extension'], 'symfony/dependency-injection', '>=8.1');
    // symfony/serializer 8.1
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/blob/8.1/UPGRADE-8.1.md#serializer
        new MethodCallRename('Symfony\Component\Serializer\Exception\PartialDenormalizationException', 'getErrors', 'getNotNormalizableValueErrors'),
    ], 'symfony/serializer', '>=8.1');
};
