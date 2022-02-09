<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
# see https://symfony.com/blog/symfony-type-declarations-return-types-and-phpunit-compatibility
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $arrayType = new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType());
    $iterableType = new \PHPStan\Type\IterableType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType());
    $nullableStringType = new \PHPStan\Type\UnionType([new \PHPStan\Type\StringType(), new \PHPStan\Type\NullType()]);
    $services->set(\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector::class)->configure([
        // see https://github.com/symfony/symfony/issues/32179
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface', 'addListener', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface', 'addListener', 2, new \PHPStan\Type\IntegerType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface', 'removeListener', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface', 'getListeners', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface', 'getListenerPriority', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface', 'hasListeners', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Application', 'setCatchExceptions', 0, new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Application', 'setAutoExit', 0, new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Application', 'setName', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Application', 'setVersion', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Application', 'register', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Application', 'get', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Application', 'has', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Application', 'findNamespace', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Application', 'find', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Application', 'all', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Application', 'getAbbreviations', 0, $arrayType),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Application', 'extractNamespace', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Application', 'extractNamespace', 1, new \PHPStan\Type\IntegerType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Application', 'setDefaultCommand', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Application', 'setDefaultCommand', 1, new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'mergeApplicationDefinition', 0, new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'addArgument', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'addArgument', 1, new \PHPStan\Type\IntegerType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'addArgument', 2, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'addOption', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'addOption', 2, new \PHPStan\Type\IntegerType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'addOption', 3, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'setName', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'setProcessTitle', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'setHidden', 0, new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'setDescription', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'setHelp', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'setAliases', 0, $iterableType),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'getSynopsis', 0, new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'addUsage', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Command\\Command', 'getHelper', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\CommandLoader\\CommandLoaderInterface', 'get', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\CommandLoader\\CommandLoaderInterface', 'has', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Input\\InputInterface', 'getArgument', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Input\\InputInterface', 'setArgument', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Input\\InputInterface', 'getOption', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Input\\InputInterface', 'setOption', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Input\\InputInterface', 'hasOption', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Input\\InputInterface', 'setInteractive', 0, new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Output\\OutputInterface', 'write', 1, new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Output\\OutputInterface', 'write', 2, new \PHPStan\Type\IntegerType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Output\\OutputInterface', 'writeln', 1, new \PHPStan\Type\IntegerType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Output\\OutputInterface', 'setVerbosity', 0, new \PHPStan\Type\IntegerType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Console\\Output\\OutputInterface', 'setDecorated', 0, new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Process\\Process', 'signal', 0, new \PHPStan\Type\IntegerType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Process\\Process', 'stop', 0, new \PHPStan\Type\FloatType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Process\\Process', 'stop', 1, new \PHPStan\Type\IntegerType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Process\\Process', 'setTty', 0, new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Process\\Process', 'setPty', 0, new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Process\\Process', 'setWorkingDirectory', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Process\\Process', 'inheritEnvironmentVariables', 0, new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Process\\Process', 'updateStatus', 0, new \PHPStan\Type\BooleanType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\EventDispatcher\\EventDispatcher', 'dispatch', 0, new \PHPStan\Type\ObjectWithoutClassType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Contracts\\Translation\\TranslatorInterface', 'setLocale', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Contracts\\Translation\\TranslatorInterface', 'trans', 0, $nullableStringType),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Contracts\\Translation\\TranslatorInterface', 'trans', 2, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Contracts\\Translation\\TranslatorInterface', 'trans', 3, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\AbstractExtension', 'getType', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\AbstractExtension', 'hasType', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\AbstractExtension', 'getTypeExtensions', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\AbstractExtension', 'hasTypeExtensions', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\DataMapperInterface', 'mapFormsToData', 0, $iterableType),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\DataMapperInterface', 'mapDataToForms', 1, $iterableType),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\Form', 'add', 1, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\Form', 'remove', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\Form', 'has', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\Form', 'get', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormBuilderInterface', 'add', 1, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormBuilderInterface', 'create', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormBuilderInterface', 'create', 1, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormBuilderInterface', 'get', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormBuilderInterface', 'remove', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormBuilderInterface', 'has', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormExtensionInterface', 'getTypeExtensions', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormExtensionInterface', 'hasTypeExtensions', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormFactory', 'create', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormFactory', 'createNamed', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormFactory', 'createNamed', 1, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormFactory', 'createForProperty', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormFactory', 'createForProperty', 1, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormFactory', 'createBuilder', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormFactory', 'createNamedBuilder', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormFactory', 'createNamedBuilder', 1, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormFactory', 'createBuilderForProperty', 0, new \PHPStan\Type\StringType()),
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Form\\FormFactory', 'createBuilderForProperty', 1, new \PHPStan\Type\StringType()),
    ]);
};
