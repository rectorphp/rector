<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\Rector\String_\SplitStringClassConstantToClassConstFetchRector;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\Set;
use Rector\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReturnArrayClassMethodToYieldRector::class)
        ->arg('$methodsByType', [
            TestCase::class => ['provideData', 'provideData*', 'dataProvider', 'dataProvider*'],
        ]);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SETS, [
        Set::CODING_STYLE, Set::CODE_QUALITY, Set::DEAD_CODE, Set::NETTE_UTILS_CODE_QUALITY, Set::SOLID, Set::PRIVATIZATION, Set::NAMING, Set::DECOMPLEX,
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
        '/Fixture/', '/Source/', '/Expected/', __DIR__ . '/packages/doctrine-annotation-generated/src/*',
    ]);

    $parameters->set(Option::EXCLUDE_RECTORS, [
        StringClassNameToClassConstantRector::class,
        SplitStringClassConstantToClassConstFetchRector::class,
        // false positives on constants used in rector-ci.php
        RemoveUnusedClassConstantRector::class,
    ]);
};
