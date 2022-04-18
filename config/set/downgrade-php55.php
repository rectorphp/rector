<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp55\Rector\ClassConstFetch\DowngradeClassConstantToStringRector;
use Rector\DowngradePhp55\Rector\Foreach_\DowngradeForeachListRector;
use Rector\DowngradePhp55\Rector\FuncCall\DowngradeBoolvalRector;
use Rector\DowngradePhp55\Rector\Isset_\DowngradeArbitraryExpressionArgsToEmptyAndIssetRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(\Rector\Core\ValueObject\PhpVersion::PHP_54);
    $services = $rectorConfig->services();
    $services->set(\Rector\DowngradePhp55\Rector\ClassConstFetch\DowngradeClassConstantToStringRector::class);
    $services->set(\Rector\DowngradePhp55\Rector\Foreach_\DowngradeForeachListRector::class);
    $services->set(\Rector\DowngradePhp55\Rector\FuncCall\DowngradeBoolvalRector::class);
    $services->set(\Rector\DowngradePhp55\Rector\Isset_\DowngradeArbitraryExpressionArgsToEmptyAndIssetRector::class);
};
