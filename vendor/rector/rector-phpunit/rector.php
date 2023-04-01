<?php

declare (strict_types=1);
namespace RectorPrefix202304;

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->importNames();
    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);
    $rectorConfig->skip([
        // for tests
        '*/Source/*',
        '*/Fixture/*',
        '*/Expected/*',
        // object types
        StringClassNameToClassConstantRector::class => [__DIR__ . '/src/Rector/MethodCall/UseSpecificWillMethodRector.php', __DIR__ . '/src/Rector/Class_/TestListenerToHooksRector.php', __DIR__ . '/src/NodeFactory/ConsecutiveAssertionFactory.php', __DIR__ . '/src/NodeAnalyzer/TestsNodeAnalyzer.php', __DIR__ . '/src/NodeFactory/DataProviderClassMethodFactory.php', __DIR__ . '/config'],
    ]);
    $rectorConfig->sets([
        // needed for DEAD_CODE list, just in split package like this
        __DIR__ . '/config/config.php',
        LevelSetList::UP_TO_PHP_81,
        SetList::DEAD_CODE,
        PHPUnitSetList::PHPUNIT_100,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::EARLY_RETURN,
        SetList::NAMING,
        SetList::TYPE_DECLARATION,
        SetList::PRIVATIZATION,
    ]);
    $rectorConfig->ruleWithConfiguration(StringClassNameToClassConstantRector::class, [
        // keep unprefixed to protected from downgrade
        'PHPUnit\\Framework\\*',
        'Prophecy\\Prophet',
    ]);
};
