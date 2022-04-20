<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp54\Rector\Closure\DowngradeStaticClosureRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeStaticClosureRector::class);
};
