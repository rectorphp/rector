<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Arguments\NodeAnalyzer\ArgumentAddingScope;
use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('Symfony\\Bridge\\Monolog\\Processor\\DebugProcessor', 'getLogs', 0, null, null, null, ArgumentAddingScope::SCOPE_METHOD_CALL), new ArgumentAdder('Symfony\\Bridge\\Monolog\\Processor\\DebugProcessor', 'countErrors', 0, 'default_value', null, null, ArgumentAddingScope::SCOPE_METHOD_CALL), new ArgumentAdder('Symfony\\Bridge\\Monolog\\Logger', 'getLogs', 0, 'default_value', null, null, ArgumentAddingScope::SCOPE_METHOD_CALL), new ArgumentAdder('Symfony\\Bridge\\Monolog\\Logger', 'countErrors', 0, 'default_value', null, null, ArgumentAddingScope::SCOPE_METHOD_CALL)]);
};
