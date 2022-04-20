<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp71\Rector\ConstFetch\DowngradePhp71JsonConstRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradePhp71JsonConstRector::class);
};
