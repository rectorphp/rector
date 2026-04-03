<?php

declare (strict_types=1);
namespace RectorPrefix202604;

use Rector\Config\RectorConfig;
use Rector\DowngradePhp86\Rector\FuncCall\DowngradeClampRector;
use Rector\ValueObject\PhpVersion;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_85);
    $rectorConfig->rule(DowngradeClampRector::class);
};
