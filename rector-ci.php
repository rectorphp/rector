<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector;
use Rector\CodingStyle\Rector\String_\SplitStringClassConstantToClassConstFetchRector;
use Rector\Core\Configuration\Option;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector;
use Rector\Restoration\ValueObject\InferParamFromClassMethodReturn;
use Rector\Set\ValueObject\SetList;
use Rector\SymfonyPhpConfig\Rector\MethodCall\AutoInPhpSymfonyConfigRector;
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
                TestCase::class => PreferThisOrSelfMethodCallRector::PREFER_THIS,
            ],
        ]]);

    $services->set(AutoInPhpSymfonyConfigRector::class);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SETS, [
        SetList::CODING_STYLE,
        SetList::CODE_QUALITY,
        SetList::CODE_QUALITY_STRICT,
        SetList::DEAD_CODE,
        SetList::NETTE_UTILS_CODE_QUALITY,
        SetList::SOLID,
        SetList::PRIVATIZATION,
        SetList::NAMING,
        SetList::ORDER,
        SetList::DEFLUENT,
        SetList::TYPE_DECLARATION,
        SetList::PHPUNIT_CODE_QUALITY,
        Setlist::SYMFONY_AUTOWIRE,
    ]);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __DIR__ . '/rules',
        __DIR__ . '/packages',
        __DIR__ . '/tests',
        __DIR__ . '/utils',
        __DIR__ . '/config/set',
    ]);

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $parameters->set(Option::SKIP, [
        StringClassNameToClassConstantRector::class,
        SplitStringClassConstantToClassConstFetchRector::class,
        // false positives on constants used in rector-ci.php
        RemoveUnusedClassConstantRector::class,

        // test paths
        '*/Fixture/*',
        '*/Source/*',
        '*/Expected/*',
        __DIR__ . '/packages/doctrine-annotation-generated/src',
        // template files
        __DIR__ . '/packages/rector-generator/templates',
        // public api
        __DIR__ . '/packages/rector-generator/src/ValueObject/RectorRecipe.php',
    ]);

    # so Rector code is still PHP 7.2 compatible
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_7_2);
    $parameters->set(Option::ENABLE_CACHE, true);
};
