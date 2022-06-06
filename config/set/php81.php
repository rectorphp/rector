<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Php81\Rector\Class_\MyCLabsClassToEnumRector;
use RectorPrefix20220606\Rector\Php81\Rector\Class_\SpatieEnumClassToEnumRector;
use RectorPrefix20220606\Rector\Php81\Rector\ClassConst\FinalizePublicClassConstantRector;
use RectorPrefix20220606\Rector\Php81\Rector\ClassMethod\NewInInitializerRector;
use RectorPrefix20220606\Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use RectorPrefix20220606\Rector\Php81\Rector\FuncCall\Php81ResourceReturnToObjectRector;
use RectorPrefix20220606\Rector\Php81\Rector\FunctionLike\IntersectionTypesRector;
use RectorPrefix20220606\Rector\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector;
use RectorPrefix20220606\Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ReturnNeverTypeRector::class);
    $rectorConfig->rule(MyCLabsClassToEnumRector::class);
    $rectorConfig->rule(MyCLabsMethodCallToEnumConstRector::class);
    $rectorConfig->rule(FinalizePublicClassConstantRector::class);
    $rectorConfig->rule(ReadOnlyPropertyRector::class);
    $rectorConfig->rule(SpatieEnumClassToEnumRector::class);
    $rectorConfig->rule(Php81ResourceReturnToObjectRector::class);
    $rectorConfig->rule(NewInInitializerRector::class);
    $rectorConfig->rule(IntersectionTypesRector::class);
    $rectorConfig->rule(NullToStrictStringFuncCallArgRector::class);
};
