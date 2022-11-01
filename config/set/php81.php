<?php

declare (strict_types=1);
namespace RectorPrefix202211;

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Php81\Rector\Class_\MyCLabsClassToEnumRector;
use Rector\Php81\Rector\Class_\SpatieEnumClassToEnumRector;
use Rector\Php81\Rector\ClassConst\FinalizePublicClassConstantRector;
use Rector\Php81\Rector\ClassMethod\NewInInitializerRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Php81\Rector\FuncCall\Php81ResourceReturnToObjectRector;
use Rector\Php81\Rector\FunctionLike\IntersectionTypesRector;
use Rector\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
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
    $rectorConfig->rule(FirstClassCallableRector::class);
};
