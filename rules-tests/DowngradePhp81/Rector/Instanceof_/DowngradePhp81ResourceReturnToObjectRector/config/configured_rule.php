<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradePhp81ResourceReturnToObjectRector::class);
};
