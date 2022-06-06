<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector;
use RectorPrefix20220606\Rector\Php55\Rector\FuncCall\GetCalledClassToSelfClassRector;
use RectorPrefix20220606\Rector\Php55\Rector\FuncCall\GetCalledClassToStaticClassRector;
use RectorPrefix20220606\Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector;
use RectorPrefix20220606\Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(StringClassNameToClassConstantRector::class);
    $rectorConfig->rule(ClassConstantToSelfClassRector::class);
    $rectorConfig->rule(PregReplaceEModifierRector::class);
    $rectorConfig->rule(GetCalledClassToSelfClassRector::class);
    $rectorConfig->rule(GetCalledClassToStaticClassRector::class);
};
