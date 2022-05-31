<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp73\Rector\ConstFetch\DowngradePhp73JsonConstRector;
use Rector\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector;
use Rector\DowngradePhp73\Rector\FuncCall\DowngradeIsCountableRector;
use Rector\DowngradePhp73\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector;
use Rector\DowngradePhp73\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector;
use Rector\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector;
use Rector\DowngradePhp73\Rector\String_\DowngradeFlexibleHeredocSyntaxRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(\Rector\Core\ValueObject\PhpVersion::PHP_72);
    $rectorConfig->rule(\Rector\DowngradePhp73\Rector\String_\DowngradeFlexibleHeredocSyntaxRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp73\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp73\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp73\Rector\FuncCall\DowngradeIsCountableRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp73\Rector\ConstFetch\DowngradePhp73JsonConstRector::class);
};
