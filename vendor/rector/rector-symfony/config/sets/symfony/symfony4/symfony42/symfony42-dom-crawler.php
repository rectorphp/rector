<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Arguments\NodeAnalyzer\ArgumentAddingScope;
use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('Symfony\\Component\\DomCrawler\\Crawler', 'children', 0, null, null, null, ArgumentAddingScope::SCOPE_METHOD_CALL)]);
};
