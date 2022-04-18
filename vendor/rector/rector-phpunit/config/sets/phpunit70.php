<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\Class_\RemoveDataProviderTestPrefixRector;
use Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector;
use Rector\Renaming\ValueObject\RenameAnnotationByType;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/phpunit-exception.php');
    $services = $rectorConfig->services();
    $services->set(\Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector::class)->configure([new \Rector\Renaming\ValueObject\RenameAnnotationByType('PHPUnit\\Framework\\TestCase', 'scenario', 'test')]);
    $services->set(\Rector\PHPUnit\Rector\Class_\RemoveDataProviderTestPrefixRector::class);
};
