<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp73\Rector\ConstFetch\DowngradePhp73JsonConstRector;
use Rector\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector;
use Rector\DowngradePhp73\Rector\FuncCall\DowngradeIsCountableRector;
use Rector\DowngradePhp73\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector;
use Rector\DowngradePhp73\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector;
use Rector\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector;
use Rector\DowngradePhp73\Rector\String_\DowngradeFlexibleHeredocSyntaxRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_72);

    $services = $rectorConfig->services();
    $services->set(DowngradeFlexibleHeredocSyntaxRector::class);
    $services->set(DowngradeListReferenceAssignmentRector::class);
    $services->set(DowngradeTrailingCommasInFunctionCallsRector::class);
    $services->set(DowngradeArrayKeyFirstLastRector::class);
    $services->set(SetCookieOptionsArrayToArgumentsRector::class);
    $services->set(DowngradeIsCountableRector::class);
    $services->set(DowngradePhp73JsonConstRector::class);
};
