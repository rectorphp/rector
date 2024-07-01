<?php

declare (strict_types=1);
namespace RectorPrefix202407;

use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;
use Rector\DowngradePhp83\Rector\ClassConst\DowngradeTypedClassConstRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_82);
    $rectorConfig->rules([DowngradeTypedClassConstRector::class]);
};
