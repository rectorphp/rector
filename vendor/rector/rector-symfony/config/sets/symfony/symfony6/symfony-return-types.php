<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

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
use Rector\StaticTypeMapper\ValueObject\Type\SimpleStaticType;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use RectorPrefix20220418\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $iterableType = new \PHPStan\Type\IterableType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType());
    $arrayType = new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType());
    $nullableStringType = new \PHPStan\Type\UnionType([new \PHPStan\Type\NullType(), new \PHPStan\Type\StringType()]);
    $nullableBooleanType = new \PHPStan\Type\UnionType([new \PHPStan\Type\NullType(), new \PHPStan\Type\BooleanType()]);
    $nullableArrayType = new \PHPStan\Type\UnionType([new \PHPStan\Type\NullType(), $arrayType]);
    $commandType = new \PHPStan\Type\ObjectType('Symfony\\Component\\Console\\Command\\Command');
    $routeCollectionType = new \PHPStan\Type\ObjectType('Symfony\\Component\\Routing\\RouteCollection\\RouteCollection');
    $httpFoundationResponseType = new \PHPStan\Type\ObjectType('Symfony\\Component\\HttpFoundation\\Response');
    $browserKitResponseType = new \PHPStan\Type\ObjectType('Symfony\\Component\\BrowserKit\\Response');
    $typeGuessType = new \PHPStan\Type\ObjectType('Symfony\\Component\\Form\\Guess\\TypeGuess');
    $nullableValueGuessType = new \PHPStan\Type\UnionType([new \PHPStan\Type\NullType(), new \PHPStan\Type\ObjectType('Symfony\\Component\\Form\\Guess\\ValueGuess')]);
    $configurationType = new \PHPStan\Type\ObjectType('Symfony\\Component\\Config\\Definition\\ConfigurationInterface');
    $scalarTypes = [$arrayType, new \PHPStan\Type\BooleanType(), new \PHPStan\Type\StringType(), new \PHPStan\Type\IntegerType(), new \PHPStan\Type\FloatType(), new \PHPStan\Type\NullType()];
    $scalarArrayObjectUnionedTypes = \array_merge($scalarTypes, [new \PHPStan\Type\ObjectType('ArrayObject')]);
    // cannot be crated with \PHPStan\Type\UnionTypeHelper::sortTypes() as ObjectType requires a class reflection we do not have here
    $unionTypeReflectionClass = new \ReflectionClass(\PHPStan\Type\UnionType::class);
    /** @var UnionType $scalarArrayObjectUnionType */
    $scalarArrayObjectUnionType = $unionTypeReflectionClass->newInstanceWithoutConstructor();
    $privatesAccessor = new \RectorPrefix20220418\Symplify\PackageBuilder\Reflection\PrivatesAccessor();
    $privatesAccessor->setPrivateProperty($scalarArrayObjectUnionType, 'types', $scalarArrayObjectUnionedTypes);
    // @see https://github.com/symfony/symfony/pull/42064
    $services = $rectorConfig->services();
    $services->set(\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector::class)->configure([
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\LoaderInterface', 'load', new \PHPStan\Type\MixedType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\Loader', 'import', new \PHPStan\Type\MixedType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\KernelInterface', 'registerBundles', $iterableType),
        // @see https://wouterj.nl/2021/09/symfony-6-native-typing#when-upgrading-to-symfony-54
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\User\\UserInterface', 'getRoles', new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType())),
        // @see https://github.com/symfony/symfony/pull/43028/files
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Console\\Helper\\HelperInterface', 'getName', new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\BrowserKit\\AbstractBrowser', 'doRequestInProcess', new \PHPStan\Type\ObjectWithoutClassType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\BrowserKit\\AbstractBrowser', 'doRequest', new \PHPStan\Type\ObjectWithoutClassType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\BrowserKit\\AbstractBrowser', 'filterRequest', new \PHPStan\Type\ObjectWithoutClassType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\BrowserKit\\AbstractBrowser', 'filterResponse', $browserKitResponseType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Config\\Definition\\ConfigurationInterface', 'getConfigTreeBuilder', new \PHPStan\Type\ObjectType('Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder')),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Config\\FileLocator', 'locate', new \PHPStan\Type\UnionType([new \PHPStan\Type\StringType(), $arrayType])),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Config\\FileLocatorInterface', 'locate', new \PHPStan\Type\UnionType([new \PHPStan\Type\StringType(), $arrayType])),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\FileLoader', 'import', new \PHPStan\Type\MixedType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\Loader', 'import', new \PHPStan\Type\MixedType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\LoaderInterface', 'load', new \PHPStan\Type\MixedType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\LoaderInterface', 'supports', new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Config\\Loader\\LoaderInterface', 'getResolver', new \PHPStan\Type\ObjectType('Symfony\\Component\\Config\\Loader\\LoaderResolverInterface')),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Config\\ResourceCheckerInterface', 'supports', new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Config\\ResourceCheckerInterface', 'isFresh', new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'doRun', new \PHPStan\Type\IntegerType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'getLongVersion', new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'add', new \PHPStan\Type\UnionType([new \PHPStan\Type\NullType(), $commandType])),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'get', $commandType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'find', $commandType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'all', $arrayType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Console\\Application', 'doRunCommand', new \PHPStan\Type\IntegerType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'isEnabled', new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'execute', new \PHPStan\Type\IntegerType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Console\\Helper\\HelperInterface', 'getName', new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Console\\Input\\InputInterface', 'getParameterOption', new \PHPStan\Type\MixedType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Console\\Input\\InputInterface', 'getArgument', new \PHPStan\Type\MixedType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Console\\Input\\InputInterface', 'getOption', new \PHPStan\Type\MixedType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Compiler\\AbstractRecursivePass', 'processValue', new \PHPStan\Type\MixedType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\ConfigurationExtensionInterface', 'getConfiguration', new \PHPStan\Type\UnionType([new \PHPStan\Type\NullType(), $configurationType])),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\Extension', 'getXsdValidationBasePath', new \PHPStan\Type\UnionType([new \PHPStan\Type\StringType(), new \PHPStan\Type\Constant\ConstantBooleanType(\false)])),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\Extension', 'getNamespace', new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\Extension', 'getConfiguration', new \PHPStan\Type\UnionType([new \PHPStan\Type\NullType(), $configurationType])),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface', 'getNamespace', new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface', 'getXsdValidationBasePath', new \PHPStan\Type\UnionType([new \PHPStan\Type\StringType(), new \PHPStan\Type\Constant\ConstantBooleanType(\false)])),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Extension\\ExtensionInterface', 'getAlias', new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\LazyProxy\\Instantiator\\InstantiatorInterface', 'instantiateProxy', new \PHPStan\Type\ObjectWithoutClassType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\EventDispatcher\\EventSubscriberInterface', 'getSubscribedEvents', $arrayType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\ExpressionLanguage\\ExpressionFunctionProviderInterface', 'getFunctions', $arrayType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Form\\AbstractExtension', 'loadTypes', $arrayType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Form\\AbstractExtension', 'loadTypeGuesser', new \PHPStan\Type\UnionType([new \PHPStan\Type\NullType(), new \PHPStan\Type\ObjectType('Symfony\\Component\\Form\\FormTypeGuesserInterface')])),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Form\\AbstractRendererEngine', 'loadResourceForBlockName', new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Form\\AbstractType', 'getBlockPrefix', new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Form\\AbstractType', 'getParent', $nullableStringType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Form\\DataTransformerInterface', 'transform', new \PHPStan\Type\MixedType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Form\\DataTransformerInterface', 'reverseTransform', new \PHPStan\Type\MixedType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormRendererEngineInterface', 'renderBlock', new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeGuesserInterface', 'guessType', new \PHPStan\Type\UnionType([new \PHPStan\Type\NullType(), $typeGuessType])),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeGuesserInterface', 'guessRequired', $nullableValueGuessType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeGuesserInterface', 'guessMaxLength', $nullableValueGuessType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeGuesserInterface', 'guessPattern', $nullableValueGuessType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeInterface', 'getBlockPrefix', new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Form\\FormTypeInterface', 'getParent', $nullableStringType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\CacheWarmer\\CacheWarmerInterface', 'isOptional', new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\CacheWarmer\\WarmableInterface', 'warmUp', $arrayType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\DataCollector\\DataCollector', 'getCasters', $arrayType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\DataCollector\\DataCollectorInterface', 'getName', new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\HttpCache\\HttpCache', 'forward', $httpFoundationResponseType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\HttpKernelBrowser', 'doRequest', $httpFoundationResponseType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\HttpKernelBrowser', 'getScript', new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\Log\\DebugLoggerInterface', 'getLogs', $arrayType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\Log\\DebugLoggerInterface', 'countErrors', new \PHPStan\Type\IntegerType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\OptionsResolver\\OptionsResolver', 'setNormalizer', new \Rector\StaticTypeMapper\ValueObject\Type\SimpleStaticType('Symfony\\Component\\OptionsResolver\\OptionsResolver')),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\OptionsResolver\\OptionsResolver', 'setAllowedValues', new \Rector\StaticTypeMapper\ValueObject\Type\SimpleStaticType('Symfony\\Component\\OptionsResolver\\OptionsResolver')),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\OptionsResolver\\OptionsResolver', 'addAllowedValues', new \Rector\StaticTypeMapper\ValueObject\Type\SimpleStaticType('Symfony\\Component\\OptionsResolver\\OptionsResolver')),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\OptionsResolver\\OptionsResolver', 'setAllowedTypes', new \Rector\StaticTypeMapper\ValueObject\Type\SimpleStaticType('Symfony\\Component\\OptionsResolver\\OptionsResolver')),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\OptionsResolver\\OptionsResolver', 'addAllowedTypes', new \Rector\StaticTypeMapper\ValueObject\Type\SimpleStaticType('Symfony\\Component\\OptionsResolver\\OptionsResolver')),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'getLength', new \PHPStan\Type\IntegerType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'getParent', new \PHPStan\Type\UnionType([new \PHPStan\Type\NullType(), new \PHPStan\Type\ObjectType('Symfony\\Component\\PropertyAccess\\PropertyPathInterface')])),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'getElements', $arrayType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'getElement', new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'isProperty', new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\PropertyAccess\\PropertyPathInterface', 'isIndex', new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\PropertyInfo\\PropertyAccessExtractorInterface', 'isReadable', $nullableBooleanType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\PropertyInfo\\PropertyAccessExtractorInterface', 'isWritable', $nullableBooleanType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\PropertyInfo\\PropertyListExtractorInterface', 'getProperties', $nullableArrayType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\PropertyInfo\\PropertyTypeExtractorInterface', 'getTypes', $nullableArrayType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Routing\\Loader\\AnnotationClassLoader', 'getDefaultRouteName', new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Routing\\Router', 'getRouteCollection', $routeCollectionType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Routing\\RouterInterface', 'getRouteCollection', $routeCollectionType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\Authentication\\RememberMe\\TokenProviderInterface', 'loadTokenBySeries', new \PHPStan\Type\ObjectType('Symfony\\Component\\Security\\Core\\Authentication\\RememberMe\\PersistentTokenInterface')),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface', 'vote', new \PHPStan\Type\IntegerType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\Exception\\AuthenticationException', 'getMessageKey', new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface', 'refreshUser', new \PHPStan\Type\ObjectType('Symfony\\Component\\Security\\Core\\User\\UserInterface')),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface', 'supportsClass', new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Security\\Http\\EntryPoint\\AuthenticationEntryPointInterface', 'start', $httpFoundationResponseType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Security\\Http\\Firewall', 'getSubscribedEvents', $arrayType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Security\\Http\\FirewallMapInterface', 'getListeners', $arrayType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Encoder\\DecoderInterface', 'decode', new \PHPStan\Type\MixedType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Encoder\\DecoderInterface', 'supportsDecoding', new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractNormalizer', 'getAllowedAttributes', new \PHPStan\Type\UnionType([$arrayType, new \PHPStan\Type\BooleanType()])),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractNormalizer', 'isAllowedAttribute', new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractNormalizer', 'instantiateObject', new \PHPStan\Type\ObjectWithoutClassType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'supportsNormalization', new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'instantiateObject', new \PHPStan\Type\ObjectWithoutClassType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'extractAttributes', $arrayType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'getAttributeValue', new \PHPStan\Type\MixedType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'supportsDenormalization', new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'denormalize', new \PHPStan\Type\MixedType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\DenormalizerInterface', 'denormalize', new \PHPStan\Type\MixedType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\DenormalizerInterface', 'supportsDenormalization', new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface', 'supportsNormalization', new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Templating\\Helper\\HelperInterface', 'getName', new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Translation\\Extractor\\AbstractFileExtractor', 'canBeExtracted', new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Translation\\Extractor\\AbstractFileExtractor', 'extractFromDirectory', $iterableType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Validator\\Constraint', 'getDefaultOption', $nullableStringType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Validator\\Constraint', 'getRequiredOptions', $arrayType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Validator\\Constraint', 'validatedBy', new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Validator\\Constraint', 'getTargets', new \PHPStan\Type\UnionType([new \PHPStan\Type\StringType(), $arrayType])),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\AbstractObjectNormalizer', 'normalize', $scalarArrayObjectUnionType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\Container', 'getParameter', new \PHPStan\Type\UnionType($scalarTypes)),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\DependencyInjection\\ContainerInterface', 'getParameter', new \PHPStan\Type\UnionType($scalarTypes)),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface', 'normalize', $scalarArrayObjectUnionType),
        new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Symfony\\Component\\Security\\Http\\Authenticator\\AuthenticatorInterface', 'authenticate', new \PHPStan\Type\ObjectType('Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Passport')),
    ]);
};
