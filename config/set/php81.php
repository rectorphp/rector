<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
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
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector::class);
    $rectorConfig->rule(\Rector\Php81\Rector\Class_\MyCLabsClassToEnumRector::class);
    $rectorConfig->rule(\Rector\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector::class);
    $rectorConfig->rule(\Rector\Php81\Rector\ClassConst\FinalizePublicClassConstantRector::class);
    $rectorConfig->rule(\Rector\Php81\Rector\Property\ReadOnlyPropertyRector::class);
    $rectorConfig->rule(\Rector\Php81\Rector\Class_\SpatieEnumClassToEnumRector::class);
    $rectorConfig->rule(\Rector\Php81\Rector\FuncCall\Php81ResourceReturnToObjectRector::class);
    $rectorConfig->rule(\Rector\Php81\Rector\ClassMethod\NewInInitializerRector::class);
    $rectorConfig->rule(\Rector\Php81\Rector\FunctionLike\IntersectionTypesRector::class);
    $rectorConfig->rule(\Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector::class);
};
