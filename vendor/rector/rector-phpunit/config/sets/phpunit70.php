<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\Class_\RemoveDataProviderTestPrefixRector;
use Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector;
use Rector\Renaming\ValueObject\RenameAnnotationByType;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/phpunit-exception.php');
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector::class, [new \Rector\Renaming\ValueObject\RenameAnnotationByType('PHPUnit\\Framework\\TestCase', 'scenario', 'test')]);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\Class_\RemoveDataProviderTestPrefixRector::class);
};
