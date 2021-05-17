<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector;
use Rector\CodingStyle\Rector\String_\SplitStringClassConstantToClassConstFetchRector;
use Rector\CodingStyle\ValueObject\PreferenceSelfThis;
use Rector\Core\Configuration\Option;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\Nette\Set\NetteSetList;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
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
    // include sets
    $containerConfigurator->import(SetList::CODING_STYLE);
    $containerConfigurator->import(SetList::CODE_QUALITY);
    $containerConfigurator->import(SetList::CODE_QUALITY_STRICT);
    $containerConfigurator->import(SetList::DEAD_CODE);
    $containerConfigurator->import(SetList::PRIVATIZATION);
    $containerConfigurator->import(SetList::NAMING);
    $containerConfigurator->import(SetList::TYPE_DECLARATION);
    $containerConfigurator->import(SetList::PHP_71);
    $containerConfigurator->import(SetList::PHP_72);
    $containerConfigurator->import(SetList::PHP_73);
    $containerConfigurator->import(SetList::EARLY_RETURN);
    $containerConfigurator->import(SetList::TYPE_DECLARATION_STRICT);
    $containerConfigurator->import(NetteSetList::NETTE_UTILS_CODE_QUALITY);
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_CODE_QUALITY);

    $services = $containerConfigurator->services();

    // PHP 7.4 and 8.0
    $services->set(TypedPropertyRector::class);
    $services->set(ClassPropertyAssignToConstructorPromotionRector::class);
    $services->set(ClosureToArrowFunctionRector::class);

    $configuration = ValueObjectInliner::inline([
        new InferParamFromClassMethodReturn(AbstractRector::class, 'refactor', 'getNodeTypes'),
    ]);
    $services->set(InferParamFromClassMethodReturnRector::class)
        ->call('configure', [[
            InferParamFromClassMethodReturnRector::INFER_PARAMS_FROM_CLASS_METHOD_RETURNS => $configuration,
        ]]);

    $services->set(ClassPropertyAssignToConstructorPromotionRector::class);

    $services->set(PreferThisOrSelfMethodCallRector::class)
        ->call('configure', [[
            PreferThisOrSelfMethodCallRector::TYPE_TO_PREFERENCE => [
                TestCase::class => PreferenceSelfThis::PREFER_THIS,
            ],
        ]]);

    $parameters = $containerConfigurator->parameters();

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

        RemoveUnreachableStatementRector::class => [
            __DIR__ . '/rules/Php70/Rector/FuncCall/MultiDirnameRector.php',
        ],

        PrivatizeLocalPropertyToPrivatePropertyRector::class => [__DIR__ . '/src/Rector/AbstractRector.php'],

        ReturnTypeDeclarationRector::class => [
            __DIR__ . '/packages/PHPStanStaticTypeMapper/TypeMapper/ArrayTypeMapper.php',
            __DIR__ . '/packages/PHPStanStaticTypeMapper/TypeMapper/ObjectTypeMapper.php',
        ],

        // test paths
        '*/Fixture/*',
        '*/Fixture*/*',
        '*/Source/*',
        '*/Source*/*',
        '*/Expected/*',
        '*/Expected*/*',
    ]);

    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, __DIR__ . '/phpstan-for-rector.neon');
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_74);
    $parameters->set(Option::ENABLE_CACHE, true);
};
