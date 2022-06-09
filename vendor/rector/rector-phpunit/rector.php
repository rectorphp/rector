<?php

declare (strict_types=1);
namespace RectorPrefix20220609;

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
return static function (RectorConfig $rectorConfig) : void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::AUTO_IMPORT_NAMES, \true);
    $parameters->set(Option::PARALLEL, \true);
    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);
    $parameters->set(Option::SKIP, [
        // for tests
        '*/Source/*',
        '*/Fixture/*',
        // object types
        StringClassNameToClassConstantRector::class => [__DIR__ . '/src/Rector/MethodCall/UseSpecificWillMethodRector.php', __DIR__ . '/src/Rector/Class_/TestListenerToHooksRector.php', __DIR__ . '/src/NodeFactory/ConsecutiveAssertionFactory.php', __DIR__ . '/src/NodeAnalyzer/TestsNodeAnalyzer.php', __DIR__ . '/src/NodeFactory/DataProviderClassMethodFactory.php', __DIR__ . '/config'],
    ]);
    // needed for DEAD_CODE list, just in split package like this
    $rectorConfig->import(__DIR__ . '/config/config.php');
    $rectorConfig->import(LevelSetList::UP_TO_PHP_81);
    $rectorConfig->import(SetList::DEAD_CODE);
    $rectorConfig->import(SetList::CODE_QUALITY);
    $rectorConfig->import(SetList::CODING_STYLE);
    $rectorConfig->import(SetList::EARLY_RETURN);
    $rectorConfig->import(SetList::NAMING);
    $rectorConfig->ruleWithConfiguration(StringClassNameToClassConstantRector::class, [
        // keep unprefixed to protected from downgrade
        'PHPUnit\\Framework\\Assert',
        'PHPUnit\\Framework\\MockObject\\*',
        'PHPUnit\\Framework\\TestCase',
        'Prophecy\\Prophet',
    ]);
};
