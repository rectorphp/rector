<?php

declare (strict_types=1);
namespace RectorPrefix202608;

use Rector\Config\RectorConfig;
use Rector\DowngradePhp84\Rector\Expression\DowngradeArrayAllRector;
use Rector\DowngradePhp84\Rector\Expression\DowngradeArrayAnyRector;
use Rector\DowngradePhp84\Rector\Expression\DowngradeArrayFindKeyRector;
use Rector\DowngradePhp84\Rector\ClassMethod\DowngradeDeprecatedAttributeRector;
use Rector\DowngradePhp84\Rector\Expression\DowngradeArrayFindRector;
use Rector\DowngradePhp84\Rector\FuncCall\DowngradeExitNamedArgumentRector;
use Rector\DowngradePhp84\Rector\FuncCall\DowngradeRoundingModeEnumRector;
use Rector\DowngradePhp84\Rector\MethodCall\DowngradeNewMethodCallWithoutParenthesesRector;
use Rector\ValueObject\PhpVersion;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_83);
    $rectorConfig->rules([DowngradeNewMethodCallWithoutParenthesesRector::class, DowngradeExitNamedArgumentRector::class, DowngradeRoundingModeEnumRector::class, DowngradeArrayAllRector::class, DowngradeArrayAnyRector::class, DowngradeArrayFindRector::class, DowngradeArrayFindKeyRector::class, DowngradeDeprecatedAttributeRector::class]);
};
