<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\DowngradePhp84\Rector\FuncCall\DowngradeRoundingModeEnumRector;
use Rector\DowngradePhp84\Rector\MethodCall\DowngradeNewMethodCallWithoutParenthesesRector;
use Rector\ValueObject\PhpVersion;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_83);
    $rectorConfig->rules([DowngradeNewMethodCallWithoutParenthesesRector::class, DowngradeRoundingModeEnumRector::class]);
};
