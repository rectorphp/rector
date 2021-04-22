<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector;
use Rector\CodingStyle\Rector\String_\SplitStringClassConstantToClassConstFetchRector;
use Rector\CodingStyle\ValueObject\PreferenceSelfThis;
use Rector\Core\Configuration\Option;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Nette\Set\NetteSetList;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector;
use Rector\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector;
use Rector\Restoration\ValueObject\InferParamFromClassMethodReturn;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $configuration = ValueObjectInliner::inline([
        new InferParamFromClassMethodReturn(AbstractRector::class, 'refactor', 'getNodeTypes'),
    ]);
    $services->set(InferParamFromClassMethodReturnRector::class)
        ->call('configure', [[
            InferParamFromClassMethodReturnRector::INFER_PARAMS_FROM_CLASS_METHOD_RETURNS => $configuration,
        ]]);

    $services->set(PreferThisOrSelfMethodCallRector::class)
        ->call('configure', [[
            PreferThisOrSelfMethodCallRector::TYPE_TO_PREFERENCE => [
                TestCase::class => PreferenceSelfThis::PREFER_THIS,
            ],
        ]]);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SETS, [
        SetList::CODING_STYLE,
        SetList::CODE_QUALITY,
        SetList::CODE_QUALITY_STRICT,
        SetList::DEAD_CODE,
        SetList::PRIVATIZATION,
        SetList::NAMING,
        SetList::TYPE_DECLARATION,
        SetList::PHP_71,
        SetList::PHP_72,
        SetList::PHP_73,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION_STRICT,
        NetteSetList::NETTE_UTILS_CODE_QUALITY,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ]);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __DIR__ . '/rules',
        __DIR__ . '/rules-tests',
        __DIR__ . '/packages',
        __DIR__ . '/packages-tests',
        __DIR__ . '/tests',
        __DIR__ . '/utils',
        __DIR__ . '/config/set',
    ]);

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $parameters->set(Option::SKIP, [
        // buggy in refactoring
        AddSeeTestAnnotationRector::class,

        StringClassNameToClassConstantRector::class,
        // some classes in config might not exist without dev dependencies
        SplitStringClassConstantToClassConstFetchRector::class,

        PrivatizeLocalPropertyToPrivatePropertyRector::class => [__DIR__ . '/src/Rector/AbstractRector.php'],

        ReturnTypeDeclarationRector::class => [
            __DIR__ . '/packages/PHPStanStaticTypeMapper/TypeMapper/ArrayTypeMapper.php',
            __DIR__ . '/packages/PHPStanStaticTypeMapper/TypeMapper/ObjectTypeMapper.php',
            // fixed in dev-main
            __DIR__ . '/packages/Testing/PHPUnit/AbstractTestCase.php',
        ],

        // test paths
        '*/Fixture/*',
        '*/Fixture/*',
        '*/Source/*',
        '*/Source*/*',
        '*/Expected/*',
        '*/Expected*/*',
    ]);

    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_73);
    $parameters->set(Option::ENABLE_CACHE, true);
};
