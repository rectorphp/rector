<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\Rector\String_\SplitStringClassConstantToClassConstFetchRector;
use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector;
use Rector\Order\Rector\Class_\OrderPrivateMethodsByUseRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Set\ValueObject\SetList;
use Rector\SymfonyPhpConfig\Rector\Closure\AddEmptyLineBetweenCallsInPhpConfigRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReturnArrayClassMethodToYieldRector::class)
        ->call('configure', [[
            ReturnArrayClassMethodToYieldRector::METHODS_BY_TYPE => [
                TestCase::class => ['provideData', 'provideData*', 'dataProvider', 'dataProvider*'],
            ],
        ],
        ]);

    $services->set(AddEmptyLineBetweenCallsInPhpConfigRector::class);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SETS, [
        SetList::CODING_STYLE,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::NETTE_UTILS_CODE_QUALITY,
        SetList::SOLID,
        SetList::PRIVATIZATION,
        SetList::NAMING,
        SetList::SYMFONY_PHP_CONFIG,
        SetList::ORDER,
        SetList::DEFLUENT,
    ]);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __DIR__ . '/rules',
        __DIR__ . '/packages',
        __DIR__ . '/tests',
        __DIR__ . '/utils',
        __DIR__ . '/compiler/src',
        __DIR__ . '/compiler/bin/compile',
        __DIR__ . '/compiler/build/scoper.inc.php',
        __DIR__ . '/config/set',
    ]);

    $parameters->set(Option::AUTOLOAD_PATHS, [__DIR__ . '/compiler/src']);

    $parameters->set(Option::EXCLUDE_PATHS, [
        '/Fixture/', '/Source/', '/Expected/',
        __DIR__ . '/packages/doctrine-annotation-generated/src/*',
        // tempalte files
        __DIR__ . '/packages/rector-generator/templates/*',
    ]);

    $parameters->set(Option::EXCLUDE_RECTORS, [
        StringClassNameToClassConstantRector::class,
        SplitStringClassConstantToClassConstFetchRector::class,
        // false positives on constants used in rector-ci.php
        RemoveUnusedClassConstantRector::class,

        OrderPrivateMethodsByUseRector::class,
    ]);
};
