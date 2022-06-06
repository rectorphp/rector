<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\PHPStan\Type\IterableType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\Rector\Arguments\NodeAnalyzer\ArgumentAddingScope;
use RectorPrefix20220606\Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use RectorPrefix20220606\Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use RectorPrefix20220606\Rector\Arguments\ValueObject\ArgumentAdder;
use RectorPrefix20220606\Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Core\ValueObject\MethodName;
use RectorPrefix20220606\Rector\Core\ValueObject\Visibility;
use RectorPrefix20220606\Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use RectorPrefix20220606\Rector\Removing\ValueObject\ArgumentRemover;
use RectorPrefix20220606\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use RectorPrefix20220606\Rector\Renaming\Rector\Name\RenameClassRector;
use RectorPrefix20220606\Rector\Renaming\ValueObject\MethodCallRename;
use RectorPrefix20220606\Rector\Symfony\Rector\MethodCall\ContainerGetToConstructorInjectionRector;
use RectorPrefix20220606\Rector\Symfony\Rector\New_\RootNodeTreeBuilderRector;
use RectorPrefix20220606\Rector\Symfony\Rector\New_\StringToArrayArgumentProcessRector;
use RectorPrefix20220606\Rector\Transform\Rector\ClassMethod\WrapReturnRector;
use RectorPrefix20220606\Rector\Transform\Rector\New_\NewToStaticCallRector;
use RectorPrefix20220606\Rector\Transform\ValueObject\NewToStaticCall;
use RectorPrefix20220606\Rector\Transform\ValueObject\WrapReturn;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use RectorPrefix20220606\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use RectorPrefix20220606\Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use RectorPrefix20220606\Rector\Visibility\ValueObject\ChangeMethodVisibility;
# https://github.com/symfony/symfony/pull/28447
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(NewToStaticCallRector::class, [new NewToStaticCall('Symfony\\Component\\HttpFoundation\\Cookie', 'Symfony\\Component\\HttpFoundation\\Cookie', 'create')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # https://github.com/symfony/symfony/commit/a7e319d9e1316e2e18843f8ce15b67a8693e5bf9
        'Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController',
        # https://github.com/symfony/symfony/commit/744bf0e7ac3ecf240d0bf055cc58f881bb0b3ec0
        'Symfony\\Bundle\\FrameworkBundle\\Command\\ContainerAwareCommand' => 'Symfony\\Component\\Console\\Command\\Command',
        'Symfony\\Component\\Translation\\TranslatorInterface' => 'Symfony\\Contracts\\Translation\\TranslatorInterface',
    ]);
    # related to "Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand" deprecation, see https://github.com/rectorphp/rector/issues/1629
    $rectorConfig->rule(ContainerGetToConstructorInjectionRector::class);
    # https://symfony.com/blog/new-in-symfony-4-2-important-deprecations
    $rectorConfig->rule(StringToArrayArgumentProcessRector::class);
    $rectorConfig->rule(RootNodeTreeBuilderRector::class);
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('Symfony\\Component\\DomCrawler\\Crawler', 'children', 0, null, null, null, ArgumentAddingScope::SCOPE_METHOD_CALL), new ArgumentAdder('Symfony\\Component\\Finder\\Finder', 'sortByName', 0, null, \false, null, ArgumentAddingScope::SCOPE_METHOD_CALL), new ArgumentAdder('Symfony\\Bridge\\Monolog\\Processor\\DebugProcessor', 'getLogs', 0, null, null, null, ArgumentAddingScope::SCOPE_METHOD_CALL), new ArgumentAdder('Symfony\\Bridge\\Monolog\\Processor\\DebugProcessor', 'countErrors', 0, 'default_value', null, null, ArgumentAddingScope::SCOPE_METHOD_CALL), new ArgumentAdder('Symfony\\Bridge\\Monolog\\Logger', 'getLogs', 0, 'default_value', null, null, ArgumentAddingScope::SCOPE_METHOD_CALL), new ArgumentAdder('Symfony\\Bridge\\Monolog\\Logger', 'countErrors', 0, 'default_value', null, null, ArgumentAddingScope::SCOPE_METHOD_CALL), new ArgumentAdder('Symfony\\Component\\Serializer\\Normalizer', 'handleCircularReference', 1, null, null, null, ArgumentAddingScope::SCOPE_METHOD_CALL), new ArgumentAdder('Symfony\\Component\\Serializer\\Normalizer', 'handleCircularReference', 2, null, null, null, ArgumentAddingScope::SCOPE_METHOD_CALL)]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Symfony\\Component\\Cache\\CacheItem', 'getPreviousTags', 'getMetadata'), new MethodCallRename('Symfony\\Component\\Form\\AbstractTypeExtension', 'getExtendedType', 'getExtendedTypes')]);
    $iterableType = new IterableType(new MixedType(), new MixedType());
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\\Component\\Form\\AbstractTypeExtension', 'getExtendedTypes', $iterableType)]);
    $rectorConfig->ruleWithConfiguration(ChangeMethodVisibilityRector::class, [new ChangeMethodVisibility('Symfony\\Component\\Form\\AbstractTypeExtension', 'getExtendedTypes', Visibility::STATIC)]);
    $rectorConfig->ruleWithConfiguration(WrapReturnRector::class, [new WrapReturn('Symfony\\Component\\Form\\AbstractTypeExtension', 'getExtendedTypes', \true)]);
    // https://github.com/symfony/symfony/commit/9493cfd5f2366dab19bbdde0d0291d0575454567
    $rectorConfig->ruleWithConfiguration(ReplaceArgumentDefaultValueRector::class, [new ReplaceArgumentDefaultValue('Symfony\\Component\\HttpFoundation\\Cookie', MethodName::CONSTRUCT, 5, \false, null), new ReplaceArgumentDefaultValue('Symfony\\Component\\HttpFoundation\\Cookie', MethodName::CONSTRUCT, 8, null, 'lax')]);
    # https://github.com/symfony/symfony/commit/f5c355e1ba399a1b3512367647d902148bdaf09f
    $rectorConfig->ruleWithConfiguration(ArgumentRemoverRector::class, [new ArgumentRemover('Symfony\\Component\\HttpKernel\\DataCollector\\ConfigDataCollector', MethodName::CONSTRUCT, 0, null), new ArgumentRemover('Symfony\\Component\\HttpKernel\\DataCollector\\ConfigDataCollector', MethodName::CONSTRUCT, 1, null)]);
};
