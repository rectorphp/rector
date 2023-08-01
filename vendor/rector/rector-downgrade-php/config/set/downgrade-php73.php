<?php

declare (strict_types=1);
namespace RectorPrefix202308;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp73\Rector\ConstFetch\DowngradePhp73JsonConstRector;
use Rector\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector;
use Rector\DowngradePhp73\Rector\FuncCall\DowngradeIsCountableRector;
use Rector\DowngradePhp73\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector;
use Rector\DowngradePhp73\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector;
use Rector\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector;
use Rector\DowngradePhp73\Rector\String_\DowngradeFlexibleHeredocSyntaxRector;
use Rector\DowngradePhp73\Rector\Unset_\DowngradeTrailingCommasInUnsetRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_72);
    $rectorConfig->rules([DowngradeFlexibleHeredocSyntaxRector::class, DowngradeListReferenceAssignmentRector::class, DowngradeTrailingCommasInFunctionCallsRector::class, DowngradeArrayKeyFirstLastRector::class, SetCookieOptionsArrayToArgumentsRector::class, DowngradeIsCountableRector::class, DowngradePhp73JsonConstRector::class, DowngradeTrailingCommasInUnsetRector::class]);
};
