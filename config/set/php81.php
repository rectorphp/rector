<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Php81\Rector\Class_\MyCLabsClassToEnumRector;
use Rector\Php81\Rector\Class_\SpatieEnumClassToEnumRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector;
use Rector\Php81\Rector\MethodCall\SpatieEnumMethodCallToEnumConstRector;
use Rector\Php81\Rector\New_\MyCLabsConstructorCallToEnumFromRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([ReturnNeverTypeRector::class, MyCLabsClassToEnumRector::class, MyCLabsMethodCallToEnumConstRector::class, MyCLabsConstructorCallToEnumFromRector::class, ReadOnlyPropertyRector::class, SpatieEnumClassToEnumRector::class, SpatieEnumMethodCallToEnumConstRector::class, NullToStrictStringFuncCallArgRector::class, FirstClassCallableRector::class]);
};
