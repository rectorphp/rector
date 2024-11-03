<?php

declare (strict_types=1);
namespace RectorPrefix202411;

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector;
use Rector\Php55\Rector\ClassConstFetch\StaticToSelfOnFinalClassRector;
use Rector\Php55\Rector\FuncCall\GetCalledClassToSelfClassRector;
use Rector\Php55\Rector\FuncCall\GetCalledClassToStaticClassRector;
use Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([StringClassNameToClassConstantRector::class, ClassConstantToSelfClassRector::class, PregReplaceEModifierRector::class, GetCalledClassToSelfClassRector::class, GetCalledClassToStaticClassRector::class, StaticToSelfOnFinalClassRector::class]);
};
