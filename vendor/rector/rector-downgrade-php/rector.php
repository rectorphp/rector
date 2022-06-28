<?php

declare (strict_types=1);
namespace RectorPrefix202206;

use Rector\CodingStyle\Enum\PreferenceSelfThis;
use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector;
use Rector\CodingStyle\ValueObject\ReturnArrayClassMethodToYield;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchRector;
use Rector\Nette\Set\NetteSetList;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php81\Rector\Class_\MyCLabsClassToEnumRector;
use Rector\Php81\Rector\Class_\SpatieEnumClassToEnumRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([LevelSetList::UP_TO_PHP_81, SetList::CODE_QUALITY, SetList::DEAD_CODE, SetList::PRIVATIZATION, SetList::NAMING, SetList::TYPE_DECLARATION, SetList::EARLY_RETURN, SetList::TYPE_DECLARATION_STRICT, NetteSetList::NETTE_UTILS_CODE_QUALITY, PHPUnitSetList::PHPUNIT_CODE_QUALITY, SetList::CODING_STYLE]);
    $rectorConfig->ruleWithConfiguration(PreferThisOrSelfMethodCallRector::class, ['PHPUnit\\Framework\\TestCase' => PreferenceSelfThis::PREFER_THIS]);
    $rectorConfig->ruleWithConfiguration(ReturnArrayClassMethodToYieldRector::class, [new ReturnArrayClassMethodToYield('PHPUnit\\Framework\\TestCase', '*provide*')]);
    // $rectorConfig->rule(RemoveJustPropertyFetchRector::class);
    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/config']);
    $rectorConfig->importNames();
    $rectorConfig->parallel();
    $rectorConfig->skip([
        StringClassNameToClassConstantRector::class,
        MyCLabsClassToEnumRector::class,
        SpatieEnumClassToEnumRector::class,
        // test paths
        '*/tests/**/Fixture/*',
        '*/tests/**/Fixture*/*',
        '*/rules-tests/**/Fixture*/*',
        // source
        '*/tests/**/Source/*',
        '*/tests/**/Source*/*',
        '*/tests/**/Expected/*',
        '*/tests/**/Expected*/*',
    ]);
    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan-for-rector.neon');
};
