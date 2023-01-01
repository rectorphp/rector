<?php

declare (strict_types=1);
namespace RectorPrefix202301;

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector;
use Rector\Php55\Rector\FuncCall\GetCalledClassToSelfClassRector;
use Rector\Php55\Rector\FuncCall\GetCalledClassToStaticClassRector;
use Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(StringClassNameToClassConstantRector::class);
    $rectorConfig->rule(ClassConstantToSelfClassRector::class);
    $rectorConfig->rule(PregReplaceEModifierRector::class);
    $rectorConfig->rule(GetCalledClassToSelfClassRector::class);
    $rectorConfig->rule(GetCalledClassToStaticClassRector::class);
};
