<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersion;
use RectorPrefix20220606\Rector\DowngradePhp55\Rector\ClassConstFetch\DowngradeClassConstantToStringRector;
use RectorPrefix20220606\Rector\DowngradePhp55\Rector\Foreach_\DowngradeForeachListRector;
use RectorPrefix20220606\Rector\DowngradePhp55\Rector\FuncCall\DowngradeBoolvalRector;
use RectorPrefix20220606\Rector\DowngradePhp55\Rector\Isset_\DowngradeArbitraryExpressionArgsToEmptyAndIssetRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_54);
    $rectorConfig->rule(DowngradeClassConstantToStringRector::class);
    $rectorConfig->rule(DowngradeForeachListRector::class);
    $rectorConfig->rule(DowngradeBoolvalRector::class);
    $rectorConfig->rule(DowngradeArbitraryExpressionArgsToEmptyAndIssetRector::class);
};
