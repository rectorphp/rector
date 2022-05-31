<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Php74\Rector\ArrayDimFetch\CurlyToSquareBracketArrayStringRector;
use Rector\Php74\Rector\Assign\NullCoalescingOperatorRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\Double\RealToFloatTypeCastRector;
use Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector;
use Rector\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector;
use Rector\Php74\Rector\FuncCall\FilterVarToAddSlashesRector;
use Rector\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector;
use Rector\Php74\Rector\Function_\ReservedFnFunctionRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Php74\Rector\StaticCall\ExportToReflectionFunctionRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Php74\Rector\Property\TypedPropertyRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\FuncCall\RenameFunctionRector::class, [
        #the_real_type
        # https://wiki.php.net/rfc/deprecations_php_7_4
        'is_real' => 'is_float',
        #apache_request_headers_function
        # https://wiki.php.net/rfc/deprecations_php_7_4
        'apache_request_headers' => 'getallheaders',
    ]);
    $rectorConfig->rule(\Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector::class);
    $rectorConfig->rule(\Rector\Php74\Rector\FuncCall\FilterVarToAddSlashesRector::class);
    $rectorConfig->rule(\Rector\Php74\Rector\StaticCall\ExportToReflectionFunctionRector::class);
    $rectorConfig->rule(\Rector\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector::class);
    $rectorConfig->rule(\Rector\Php74\Rector\Double\RealToFloatTypeCastRector::class);
    $rectorConfig->rule(\Rector\Php74\Rector\Assign\NullCoalescingOperatorRector::class);
    $rectorConfig->rule(\Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector::class);
    $rectorConfig->rule(\Rector\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector::class);
    $rectorConfig->rule(\Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector::class);
    $rectorConfig->rule(\Rector\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector::class);
    $rectorConfig->rule(\Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector::class);
    $rectorConfig->rule(\Rector\Php74\Rector\ArrayDimFetch\CurlyToSquareBracketArrayStringRector::class);
    $rectorConfig->rule(\Rector\Php74\Rector\Function_\ReservedFnFunctionRector::class);
};
