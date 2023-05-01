<?php

declare (strict_types=1);
namespace RectorPrefix202305;

use Rector\Config\RectorConfig;
use Rector\Php74\Rector\ArrayDimFetch\CurlyToSquareBracketArrayStringRector;
use Rector\Php74\Rector\Assign\NullCoalescingOperatorRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\Double\RealToFloatTypeCastRector;
use Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector;
use Rector\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector;
use Rector\Php74\Rector\FuncCall\FilterVarToAddSlashesRector;
use Rector\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector;
use Rector\Php74\Rector\FuncCall\MoneyFormatToNumberFormatRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php74\Rector\StaticCall\ExportToReflectionFunctionRector;
use Rector\Php74\Rector\Ternary\ParenthesizeNestedTernaryRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        #the_real_type
        # https://wiki.php.net/rfc/deprecations_php_7_4
        'is_real' => 'is_float',
        #apache_request_headers_function
        # https://wiki.php.net/rfc/deprecations_php_7_4
        'apache_request_headers' => 'getallheaders',
    ]);
    $rectorConfig->rules([ArrayKeyExistsOnPropertyRector::class, FilterVarToAddSlashesRector::class, ExportToReflectionFunctionRector::class, MbStrrposEncodingArgumentPositionRector::class, RealToFloatTypeCastRector::class, NullCoalescingOperatorRector::class, ClosureToArrowFunctionRector::class, ArraySpreadInsteadOfArrayMergeRector::class, AddLiteralSeparatorToNumberRector::class, ChangeReflectionTypeToStringToGetNameRector::class, RestoreDefaultNullToNullableTypePropertyRector::class, CurlyToSquareBracketArrayStringRector::class, MoneyFormatToNumberFormatRector::class, ParenthesizeNestedTernaryRector::class, TypedPropertyFromAssignsRector::class]);
};
