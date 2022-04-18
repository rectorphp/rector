<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES, \true);
    $parameters->set(\Rector\Core\Configuration\Option::PARALLEL, \true);
    $parameters->set(\Rector\Core\Configuration\Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);
    $parameters->set(\Rector\Core\Configuration\Option::SKIP, [
        // for tests
        '*/Source/*',
        '*/Fixture/*',
        // object types
        \Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class => [__DIR__ . '/src/Rector/MethodCall/WithConsecutiveArgToArrayRector.php', __DIR__ . '/src/Rector/MethodCall/UseSpecificWillMethodRector.php', __DIR__ . '/src/Rector/Class_/TestListenerToHooksRector.php', __DIR__ . '/src/NodeFactory/ConsecutiveAssertionFactory.php', __DIR__ . '/src/NodeAnalyzer/TestsNodeAnalyzer.php', __DIR__ . '/src/NodeFactory/DataProviderClassMethodFactory.php', __DIR__ . '/config'],
    ]);
    // needed for DEAD_CODE list, just in split package like this
    $rectorConfig->import(__DIR__ . '/config/config.php');
    $rectorConfig->import(\Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_81);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::DEAD_CODE);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::CODE_QUALITY);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::CODING_STYLE);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::EARLY_RETURN);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::NAMING);
    $services = $rectorConfig->services();
    $services->set(\Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class)->configure([
        // keep unprefixed to protected from downgrade
        'PHPUnit\\Framework\\Assert',
        'PHPUnit\\Framework\\MockObject\\*',
        'PHPUnit\\Framework\\TestCase',
        'Prophecy\\Prophet',
    ]);
};
