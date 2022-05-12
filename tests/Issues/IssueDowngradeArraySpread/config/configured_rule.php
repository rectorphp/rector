<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector;
use Rector\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeArraySpreadStringKeyRector::class);
    $rectorConfig->rule(DowngradeArraySpreadRector::class);

    $rectorConfig->phpVersion(PhpVersion::PHP_81);
};
