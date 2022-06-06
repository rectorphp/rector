<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Php74\Rector\ArrayDimFetch\CurlyToSquareBracketArrayStringRector;
use RectorPrefix20220606\Rector\Php74\Rector\Assign\NullCoalescingOperatorRector;
use RectorPrefix20220606\Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use RectorPrefix20220606\Rector\Php74\Rector\Double\RealToFloatTypeCastRector;
use RectorPrefix20220606\Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector;
use RectorPrefix20220606\Rector\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector;
use RectorPrefix20220606\Rector\Php74\Rector\FuncCall\FilterVarToAddSlashesRector;
use RectorPrefix20220606\Rector\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector;
use RectorPrefix20220606\Rector\Php74\Rector\Function_\ReservedFnFunctionRector;
use RectorPrefix20220606\Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use RectorPrefix20220606\Rector\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector;
use RectorPrefix20220606\Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use RectorPrefix20220606\Rector\Php74\Rector\Property\TypedPropertyRector;
use RectorPrefix20220606\Rector\Php74\Rector\StaticCall\ExportToReflectionFunctionRector;
use RectorPrefix20220606\Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(TypedPropertyRector::class);
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        #the_real_type
        # https://wiki.php.net/rfc/deprecations_php_7_4
        'is_real' => 'is_float',
        #apache_request_headers_function
        # https://wiki.php.net/rfc/deprecations_php_7_4
        'apache_request_headers' => 'getallheaders',
    ]);
    $rectorConfig->rule(ArrayKeyExistsOnPropertyRector::class);
    $rectorConfig->rule(FilterVarToAddSlashesRector::class);
    $rectorConfig->rule(ExportToReflectionFunctionRector::class);
    $rectorConfig->rule(MbStrrposEncodingArgumentPositionRector::class);
    $rectorConfig->rule(RealToFloatTypeCastRector::class);
    $rectorConfig->rule(NullCoalescingOperatorRector::class);
    $rectorConfig->rule(ClosureToArrowFunctionRector::class);
    $rectorConfig->rule(ArraySpreadInsteadOfArrayMergeRector::class);
    $rectorConfig->rule(AddLiteralSeparatorToNumberRector::class);
    $rectorConfig->rule(ChangeReflectionTypeToStringToGetNameRector::class);
    $rectorConfig->rule(RestoreDefaultNullToNullableTypePropertyRector::class);
    $rectorConfig->rule(CurlyToSquareBracketArrayStringRector::class);
    $rectorConfig->rule(ReservedFnFunctionRector::class);
};
