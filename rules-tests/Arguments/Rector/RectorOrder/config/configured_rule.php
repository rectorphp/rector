<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\MethodCall\AssertComparisonToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertFalseStrposToContainsRector;
use Rector\PHPUnit\Rector\MethodCall\AssertSameBoolNullToSpecificMethodRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(AssertComparisonToSpecificMethodRector::class);
    $rectorConfig->rule(AssertSameBoolNullToSpecificMethodRector::class);
    $rectorConfig->rule(AssertFalseStrposToContainsRector::class);
};
