<?php

declare (strict_types=1);
namespace RectorPrefix202312;

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->importNames();
    $rectorConfig->removeUnusedImports();
    $rectorConfig->paths([__DIR__ . '/config', __DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/rules', __DIR__ . '/rules-tests', __DIR__ . '/rector.php', __DIR__ . '/ecs.php']);
    $rectorConfig->skip([
        // for tests
        '*/Source/*',
        '*/Fixture/*',
        '*/Expected/*',
        // object types
        StringClassNameToClassConstantRector::class => [__DIR__ . '/src/Rector/Class_/TestListenerToHooksRector.php', __DIR__ . '/src/NodeAnalyzer/TestsNodeAnalyzer.php', __DIR__ . '/config'],
    ]);
    $rectorConfig->sets([LevelSetList::UP_TO_PHP_81, SetList::DEAD_CODE, PHPUnitSetList::PHPUNIT_100, PHPUnitSetList::PHPUNIT_CODE_QUALITY, SetList::CODE_QUALITY, SetList::CODING_STYLE, SetList::EARLY_RETURN, SetList::NAMING, SetList::TYPE_DECLARATION, SetList::PRIVATIZATION]);
    $rectorConfig->ruleWithConfiguration(StringClassNameToClassConstantRector::class, [
        // keep unprefixed to protected from downgrade
        'PHPUnit\\Framework\\*',
        'Prophecy\\Prophet',
    ]);
};
