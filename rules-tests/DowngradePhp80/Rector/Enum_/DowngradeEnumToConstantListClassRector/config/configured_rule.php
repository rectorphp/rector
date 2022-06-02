<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeEnumToConstantListClassRector::class);
};
