<?php

declare (strict_types=1);
namespace RectorPrefix202305;

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
use Rector\Config\RectorConfig;
use Rector\Core\Util\Reflection\PrivatesAccessor;
use Rector\StaticTypeMapper\ValueObject\Type\SimpleStaticType;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig) : void {
    $iterableType = new IterableType(new MixedType(), new MixedType());
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $nullableStringType = new UnionType([new NullType(), new StringType()]);
    $nullableBooleanType = new UnionType([new NullType(), new BooleanType()]);
    $nullableArrayType = new UnionType([new NullType(), $arrayType]);
    $commandType = new ObjectType('Symfony\\Component\\Console\\Command\\Command');
    $routeCollectionType = new ObjectType('Symfony\\Component\\Routing\\RouteCollection');
    $httpFoundationResponseType = new ObjectType('Symfony\\Component\\HttpFoundation\\Response');
    $browserKitResponseType = new ObjectType('Symfony\\Component\\BrowserKit\\Response');
    $typeGuessType = new ObjectType('Symfony\\Component\\Form\\Guess\\TypeGuess');
    $nullableValueGuessType = new UnionType([new NullType(), new ObjectType('Symfony\\Component\\Form\\Guess\\ValueGuess')]);
    $configurationType = new ObjectType('Symfony\\Component\\Config\\Definition\\ConfigurationInterface');
    $scalarTypes = [$arrayType, new BooleanType(), new StringType(), new IntegerType(), new FloatType(), new NullType()];
    $scalarArrayObjectUnionedTypes = \array_merge($scalarTypes, [new ObjectType('ArrayObject')]);
    // cannot be crated with \PHPStan\Type\UnionTypeHelper::sortTypes() as ObjectType requires a class reflection we do not have here
    $unionTypeReflectionClass = new \ReflectionClass(UnionType::class);
    /** @var UnionType $scalarArrayObjectUnionType */
    $scalarArrayObjectUnionType = $unionTypeReflectionClass->newInstanceWithoutConstructor();
    $privatesAccessor = new PrivatesAccessor();
    $privatesAccessor->setPrivateProperty($scalarArrayObjectUnionType, 'types', $scalarArrayObjectUnionedTypes);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [
        new AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\LoaderInterface', 'load', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\Loader', 'import', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\KernelInterface', 'registerBundles', $iterableType),
        // @see https://wouterj.nl/2021/09/symfony-6-native-typing#when-upgrading-to-symfony-54
        new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\User\\UserInterface', 'getRoles', new ArrayType(new MixedType(), new MixedType())),
        // @see https://github.com/symfony/symfony/pull/43028/files
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Helper\\HelperInterface', 'getName', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\BrowserKit\\AbstractBrowser', 'doRequestInProcess', new ObjectWithoutClassType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\BrowserKit\\AbstractBrowser', 'doRequest', new ObjectWithoutClassType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\BrowserKit\\AbstractBrowser', 'filterRequest', new ObjectWithoutClassType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\BrowserKit\\AbstractBrowser', 'filterResponse', $browserKitResponseType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Config\\Definition\\ConfigurationInterface', 'getConfigTreeBuilder', new ObjectType('Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder')),
        new AddReturnTypeDeclaration('Symfony\\Component\\Config\\FileLocator', 'locate', new UnionType([new StringType(), $arrayType])),
        new AddReturnTypeDeclaration('Symfony\\Component\\Config\\FileLocatorInterface', 'locate', new UnionType([new StringType(), $arrayType])),
        new AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\FileLoader', 'import', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\Loader', 'import', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\LoaderInterface', 'load', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\LoaderInterface', 'supports', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\LoaderInterface', 'getResolver', new ObjectType('Symfony\\Component\\Config\\Loader\\LoaderResolverInterface')),
        new AddReturnTypeDeclaration('Symfony\\Component\\Config\\ResourceCheckerInterface', 'supports', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Config\\ResourceCheckerInterface', 'isFresh', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'doRun', new IntegerType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'getLongVersion', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'add', new UnionType([new NullType(), $commandType])),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'get', $commandType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'find', $commandType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'all', $arrayType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'doRunCommand', new IntegerType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'isEnabled', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'execute', new IntegerType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Helper\\HelperInterface', 'getName', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Input\\InputInterface', 'getParameterOption', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Input\\InputInterface', 'getArgument', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Console\\Input\\InputInterface', 'getOption', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Compiler\\AbstractRecursivePass', 'processValue', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\ConfigurationExtensionInterface', 'getConfiguration', new UnionType([new NullType(), $configurationType])),
        new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\Extension', 'getXsdValidationBasePath', new UnionType([new StringType(), new ConstantBooleanType(\false)])),
        new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\Extension', 'getNamespace', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\Extension', 'getConfiguration', new UnionType([new NullType(), $configurationType])),
        new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface', 'getNamespace', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface', 'getXsdValidationBasePath', new UnionType([new StringType(), new ConstantBooleanType(\false)])),
        new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface', 'getAlias', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\LazyProxy\\Instantiator\\InstantiatorInterface', 'instantiateProxy', new ObjectWithoutClassType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\EventDispatcher\\EventSubscriberInterface', 'getSubscribedEvents', $arrayType),
        new AddReturnTypeDeclaration('Symfony\\Component\\ExpressionLanguage\\ExpressionFunctionProviderInterface', 'getFunctions', $arrayType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Form\\AbstractExtension', 'loadTypes', $arrayType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Form\\AbstractExtension', 'loadTypeGuesser', new UnionType([new NullType(), new ObjectType('Symfony\\Component\\Form\\FormTypeGuesserInterface')])),
        new AddReturnTypeDeclaration('Symfony\\Component\\Form\\AbstractRendererEngine', 'loadResourceForBlockName', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Form\\AbstractType', 'getBlockPrefix', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Form\\AbstractType', 'getParent', $nullableStringType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Form\\DataTransformerInterface', 'transform', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Form\\DataTransformerInterface', 'reverseTransform', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormRendererEngineInterface', 'renderBlock', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeGuesserInterface', 'guessType', new UnionType([new NullType(), $typeGuessType])),
        new AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeGuesserInterface', 'guessRequired', $nullableValueGuessType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeGuesserInterface', 'guessMaxLength', $nullableValueGuessType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeGuesserInterface', 'guessPattern', $nullableValueGuessType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeInterface', 'getBlockPrefix', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeInterface', 'getParent', $nullableStringType),
        new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\CacheWarmer\\CacheWarmerInterface', 'isOptional', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\CacheWarmer\\WarmableInterface', 'warmUp', $arrayType),
        new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\DataCollector\\DataCollector', 'getCasters', $arrayType),
        new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\DataCollector\\DataCollectorInterface', 'getName', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\HttpCache\\HttpCache', 'forward', $httpFoundationResponseType),
        new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\HttpKernelBrowser', 'doRequest', $httpFoundationResponseType),
        new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\HttpKernelBrowser', 'getScript', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\Log\\DebugLoggerInterface', 'getLogs', $arrayType),
        new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\Log\\DebugLoggerInterface', 'countErrors', new IntegerType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\OptionsResolver\\OptionsResolver', 'setNormalizer', new SimpleStaticType('Symfony\\Component\\OptionsResolver\\OptionsResolver')),
        new AddReturnTypeDeclaration('Symfony\\Component\\OptionsResolver\\OptionsResolver', 'setAllowedValues', new SimpleStaticType('Symfony\\Component\\OptionsResolver\\OptionsResolver')),
        new AddReturnTypeDeclaration('Symfony\\Component\\OptionsResolver\\OptionsResolver', 'addAllowedValues', new SimpleStaticType('Symfony\\Component\\OptionsResolver\\OptionsResolver')),
        new AddReturnTypeDeclaration('Symfony\\Component\\OptionsResolver\\OptionsResolver', 'setAllowedTypes', new SimpleStaticType('Symfony\\Component\\OptionsResolver\\OptionsResolver')),
        new AddReturnTypeDeclaration('Symfony\\Component\\OptionsResolver\\OptionsResolver', 'addAllowedTypes', new SimpleStaticType('Symfony\\Component\\OptionsResolver\\OptionsResolver')),
        new AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'getLength', new IntegerType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'getParent', new UnionType([new NullType(), new ObjectType('Symfony\\Component\\PropertyAccess\\PropertyPathInterface')])),
        new AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'getElements', $arrayType),
        new AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'getElement', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'isProperty', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'isIndex', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\PropertyInfo\\PropertyAccessExtractorInterface', 'isReadable', $nullableBooleanType),
        new AddReturnTypeDeclaration('Symfony\\Component\\PropertyInfo\\PropertyAccessExtractorInterface', 'isWritable', $nullableBooleanType),
        new AddReturnTypeDeclaration('Symfony\\Component\\PropertyInfo\\PropertyListExtractorInterface', 'getProperties', $nullableArrayType),
        new AddReturnTypeDeclaration('Symfony\\Component\\PropertyInfo\\PropertyTypeExtractorInterface', 'getTypes', $nullableArrayType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Routing\\Loader\\AnnotationClassLoader', 'getDefaultRouteName', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Routing\\Router', 'getRouteCollection', $routeCollectionType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Routing\\RouterInterface', 'getRouteCollection', $routeCollectionType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\Authentication\\RememberMe\\TokenProviderInterface', 'loadTokenBySeries', new ObjectType('Symfony\\Component\\Security\\Core\\Authentication\\RememberMe\\PersistentTokenInterface')),
        new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface', 'vote', new IntegerType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\Exception\\AuthenticationException', 'getMessageKey', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface', 'refreshUser', new ObjectType('Symfony\\Component\\Security\\Core\\User\\UserInterface')),
        new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface', 'supportsClass', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Http\\EntryPoint\\AuthenticationEntryPointInterface', 'start', $httpFoundationResponseType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Http\\Firewall', 'getSubscribedEvents', $arrayType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Http\\FirewallMapInterface', 'getListeners', $arrayType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Encoder\\DecoderInterface', 'decode', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Encoder\\DecoderInterface', 'supportsDecoding', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractNormalizer', 'getAllowedAttributes', new UnionType([$arrayType, new BooleanType()])),
        new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractNormalizer', 'isAllowedAttribute', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractNormalizer', 'instantiateObject', new ObjectWithoutClassType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'supportsNormalization', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'instantiateObject', new ObjectWithoutClassType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'extractAttributes', $arrayType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'getAttributeValue', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'supportsDenormalization', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'denormalize', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\DenormalizerInterface', 'denormalize', new MixedType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\DenormalizerInterface', 'supportsDenormalization', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface', 'supportsNormalization', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Templating\\Helper\\HelperInterface', 'getName', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Translation\\Extractor\\AbstractFileExtractor', 'canBeExtracted', new BooleanType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Translation\\Extractor\\AbstractFileExtractor', 'extractFromDirectory', $iterableType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Validator\\Constraint', 'getDefaultOption', $nullableStringType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Validator\\Constraint', 'getRequiredOptions', $arrayType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Validator\\Constraint', 'validatedBy', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Validator\\Constraint', 'getTargets', new UnionType([new StringType(), $arrayType])),
        new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'normalize', $scalarArrayObjectUnionType),
        new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Container', 'getParameter', new UnionType($scalarTypes)),
        new AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\ContainerInterface', 'getParameter', new UnionType($scalarTypes)),
        new AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface', 'normalize', $scalarArrayObjectUnionType),
        new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Http\\Authenticator\\AuthenticatorInterface', 'authenticate', new ObjectType('Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Passport')),
    ]);
};
