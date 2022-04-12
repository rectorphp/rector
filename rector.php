<?php

declare(strict_types=1);

use Rector\CodingStyle\Enum\PreferenceSelfThis;
use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector;
use Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use Rector\Config\RectorConfig;
use Rector\Nette\Set\NetteSetList;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php81\Rector\Class_\MyCLabsClassToEnumRector;
use Rector\Php81\Rector\Class_\SpatieEnumClassToEnumRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::PRIVATIZATION,
        SetList::NAMING,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION_STRICT,
        NetteSetList::NETTE_UTILS_CODE_QUALITY,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        SetList::CODING_STYLE,
    ]);

    $rectorConfig->ruleWithConfiguration(
        PreferThisOrSelfMethodCallRector::class,
        [
            'PHPUnit\Framework\TestCase' => PreferenceSelfThis::PREFER_THIS(),
        ]
    );

    $rectorConfig->ruleWithConfiguration(ReturnArrayClassMethodToYieldRector::class, [
        new ReturnArrayClassMethodToYield('PHPUnit\Framework\TestCase', '*provide*'),
    ]);

    $rectorConfig->paths([
        __DIR__ . '/bin',
        __DIR__ . '/src',
        __DIR__ . '/rules',
        __DIR__ . '/rules-tests',
        __DIR__ . '/packages',
        __DIR__ . '/packages-tests',
        __DIR__ . '/tests',
        __DIR__ . '/utils',
        __DIR__ . '/config',
        __DIR__ . '/scoper.php',
    ]);

    $rectorConfig->importNames();
    $rectorConfig->parallel();

    $rectorConfig->skip([
        StringClassNameToClassConstantRector::class,

        FinalizeClassesWithoutChildrenRector::class => [
            __DIR__ . '/rules/DowngradePhp74/Rector/Array_/DowngradeArraySpreadRector.php',
        ],

        MyCLabsClassToEnumRector::class,
        SpatieEnumClassToEnumRector::class,

        // test paths
        '*/tests/**/Fixture/*',
        '*/rules-tests/**/Fixture/*',
        '*/packages-tests/**/Fixture/*',
        '*/tests/**/Fixture*/*',
        '*/rules-tests/**/Fixture*/*',
        '*/packages-tests/**/Fixture*/*',
        // source
        '*/tests/**/Source/*',
        '*/rules-tests/**/Source/*',
        '*/packages-tests/**/Source/*',
        '*/tests/**/Source*/*',
        '*/rules-tests/**/Source*/*',
        '*/packages-tests/**/Source*/*',
        '*/tests/**/Expected/*',
        '*/rules-tests/**/Expected/*',
        '*/packages-tests/**/Expected/*',
        '*/tests/**/Expected*/*',
        '*/rules-tests/**/Expected*/*',
        '*/packages-tests/**/Expected*/*',
        __DIR__ . '/tests/PhpUnit/MultipleFilesChangedTrait/MultipleFilesChangedTraitTest.php',

        // to keep original API from PHPStan untouched
        __DIR__ . '/packages/Caching/ValueObject/Storage/FileCacheStorage.php',
    ]);

    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan-for-rector.neon');
};
