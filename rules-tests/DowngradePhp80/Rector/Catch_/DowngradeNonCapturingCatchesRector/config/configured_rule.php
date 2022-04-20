<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeNonCapturingCatchesRector::class);
};
