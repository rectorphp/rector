<?php

declare (strict_types=1);
namespace RectorPrefix202312;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\PHPUnit70\Rector\Class_\RemoveDataProviderTestPrefixRector;
use Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector;
use Rector\Renaming\ValueObject\RenameAnnotationByType;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameAnnotationRector::class, [new RenameAnnotationByType('PHPUnit\\Framework\\TestCase', 'scenario', 'test')]);
    $rectorConfig->rule(RemoveDataProviderTestPrefixRector::class);
};
