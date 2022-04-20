<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp70\Rector\TryCatch\DowngradeCatchThrowableRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeCatchThrowableRector::class);
};
