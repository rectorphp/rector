<?php

declare (strict_types=1);
namespace RectorPrefix202211;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\Class_\RemoveDataProviderTestPrefixRector;
use Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector;
use Rector\Renaming\ValueObject\RenameAnnotationByType;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/phpunit-exception.php');
    $rectorConfig->ruleWithConfiguration(RenameAnnotationRector::class, [new RenameAnnotationByType('PHPUnit\\Framework\\TestCase', 'scenario', 'test')]);
    $rectorConfig->rule(RemoveDataProviderTestPrefixRector::class);
};
