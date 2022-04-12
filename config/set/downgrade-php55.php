<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp55\Rector\ClassConstFetch\DowngradeClassConstantToStringRector;
use Rector\DowngradePhp55\Rector\Foreach_\DowngradeForeachListRector;
use Rector\DowngradePhp55\Rector\FuncCall\DowngradeBoolvalRector;
use Rector\DowngradePhp55\Rector\Isset_\DowngradeArbitraryExpressionArgsToEmptyAndIssetRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_54);

    $services = $rectorConfig->services();
    $services->set(DowngradeClassConstantToStringRector::class);
    $services->set(DowngradeForeachListRector::class);
    $services->set(DowngradeBoolvalRector::class);
    $services->set(DowngradeArbitraryExpressionArgsToEmptyAndIssetRector::class);
};
