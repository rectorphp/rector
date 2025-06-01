<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Arguments\NodeAnalyzer\ArgumentAddingScope;
use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('Symfony\\Component\\Finder\\Finder', 'sortByName', 0, null, \false, null, ArgumentAddingScope::SCOPE_METHOD_CALL)]);
};
