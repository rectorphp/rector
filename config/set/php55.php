<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector;
use Rector\Php55\Rector\FuncCall\GetCalledClassToSelfClassRector;
use Rector\Php55\Rector\FuncCall\GetCalledClassToStaticClassRector;
use Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    $services->set(\Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class);
    $services->set(\Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector::class);
    $services->set(\Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector::class);
    $services->set(\Rector\Php55\Rector\FuncCall\GetCalledClassToSelfClassRector::class);
    $services->set(\Rector\Php55\Rector\FuncCall\GetCalledClassToStaticClassRector::class);
};
