<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector;
use Rector\Php55\Rector\FuncCall\GetCalledClassToSelfClassRector;
use Rector\Php55\Rector\FuncCall\GetCalledClassToStaticClassRector;
use Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class);
    $rectorConfig->rule(\Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector::class);
    $rectorConfig->rule(\Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector::class);
    $rectorConfig->rule(\Rector\Php55\Rector\FuncCall\GetCalledClassToSelfClassRector::class);
    $rectorConfig->rule(\Rector\Php55\Rector\FuncCall\GetCalledClassToStaticClassRector::class);
};
