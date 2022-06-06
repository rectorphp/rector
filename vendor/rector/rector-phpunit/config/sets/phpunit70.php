<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\PHPUnit\Rector\Class_\RemoveDataProviderTestPrefixRector;
use RectorPrefix20220606\Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector;
use RectorPrefix20220606\Rector\Renaming\ValueObject\RenameAnnotationByType;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/phpunit-exception.php');
    $rectorConfig->ruleWithConfiguration(RenameAnnotationRector::class, [new RenameAnnotationByType('PHPUnit\\Framework\\TestCase', 'scenario', 'test')]);
    $rectorConfig->rule(RemoveDataProviderTestPrefixRector::class);
};
