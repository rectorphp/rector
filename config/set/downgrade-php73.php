<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersion;
use RectorPrefix20220606\Rector\DowngradePhp73\Rector\ConstFetch\DowngradePhp73JsonConstRector;
use RectorPrefix20220606\Rector\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector;
use RectorPrefix20220606\Rector\DowngradePhp73\Rector\FuncCall\DowngradeIsCountableRector;
use RectorPrefix20220606\Rector\DowngradePhp73\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector;
use RectorPrefix20220606\Rector\DowngradePhp73\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector;
use RectorPrefix20220606\Rector\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector;
use RectorPrefix20220606\Rector\DowngradePhp73\Rector\String_\DowngradeFlexibleHeredocSyntaxRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_72);
    $rectorConfig->rule(DowngradeFlexibleHeredocSyntaxRector::class);
    $rectorConfig->rule(DowngradeListReferenceAssignmentRector::class);
    $rectorConfig->rule(DowngradeTrailingCommasInFunctionCallsRector::class);
    $rectorConfig->rule(DowngradeArrayKeyFirstLastRector::class);
    $rectorConfig->rule(SetCookieOptionsArrayToArgumentsRector::class);
    $rectorConfig->rule(DowngradeIsCountableRector::class);
    $rectorConfig->rule(DowngradePhp73JsonConstRector::class);
};
