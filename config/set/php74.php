<?php

declare (strict_types=1);
namespace RectorPrefix202510;

use PhpParser\Node\Expr\Cast\Double;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\ArrayDimFetch\CurlyToSquareBracketArrayStringRector;
use Rector\Php74\Rector\Assign\NullCoalescingOperatorRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector;
use Rector\Php74\Rector\FuncCall\FilterVarToAddSlashesRector;
use Rector\Php74\Rector\FuncCall\HebrevcToNl2brHebrevRector;
use Rector\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector;
use Rector\Php74\Rector\FuncCall\MoneyFormatToNumberFormatRector;
use Rector\Php74\Rector\FuncCall\RestoreIncludePathToIniRestoreRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php74\Rector\StaticCall\ExportToReflectionFunctionRector;
use Rector\Php74\Rector\Ternary\ParenthesizeNestedTernaryRector;
use Rector\Renaming\Rector\Cast\RenameCastRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Renaming\ValueObject\RenameCast;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        #the_real_type
        # https://wiki.php.net/rfc/deprecations_php_7_4
        'is_real' => 'is_float',
    ]);
    $rectorConfig->rules([ArrayKeyExistsOnPropertyRector::class, FilterVarToAddSlashesRector::class, ExportToReflectionFunctionRector::class, MbStrrposEncodingArgumentPositionRector::class, NullCoalescingOperatorRector::class, ClosureToArrowFunctionRector::class, RestoreDefaultNullToNullableTypePropertyRector::class, CurlyToSquareBracketArrayStringRector::class, MoneyFormatToNumberFormatRector::class, ParenthesizeNestedTernaryRector::class, RestoreIncludePathToIniRestoreRector::class, HebrevcToNl2brHebrevRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameCastRector::class, [new RenameCast(Double::class, Double::KIND_REAL, Double::KIND_FLOAT)]);
};
