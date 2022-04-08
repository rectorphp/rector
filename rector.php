<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Rector\CodingStyle\Enum\PreferenceSelfThis;
use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector;
use Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Nette\Set\NetteSetList;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php81\Rector\Class_\MyCLabsClassToEnumRector;
use Rector\Php81\Rector\Class_\SpatieEnumClassToEnumRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    // include the latest PHP version + all bellow in one config!
    $rectorConfig->import(LevelSetList::UP_TO_PHP_81);

    // include sets
    $rectorConfig->import(SetList::CODING_STYLE);
    $rectorConfig->import(SetList::CODE_QUALITY);
    $rectorConfig->import(SetList::DEAD_CODE);
    $rectorConfig->import(SetList::PRIVATIZATION);
    $rectorConfig->import(SetList::NAMING);
    $rectorConfig->import(SetList::TYPE_DECLARATION);
    $rectorConfig->import(SetList::EARLY_RETURN);
    $rectorConfig->import(SetList::TYPE_DECLARATION_STRICT);
    $rectorConfig->import(NetteSetList::NETTE_UTILS_CODE_QUALITY);
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_CODE_QUALITY);

    $services = $rectorConfig->services();

    // phpunit
    $services->set(PreferThisOrSelfMethodCallRector::class)
        ->configure([
            TestCase::class => PreferenceSelfThis::PREFER_THIS(),
        ]);

    $services->set(ReturnArrayClassMethodToYieldRector::class)
        ->configure([new ReturnArrayClassMethodToYield('PHPUnit\Framework\TestCase', '*provide*')]);

    $parameters = $rectorConfig->parameters();

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

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
    $parameters->set(Option::PARALLEL, true);

    $parameters->set(Option::SKIP, [
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

    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, __DIR__ . '/phpstan-for-rector.neon');
};
