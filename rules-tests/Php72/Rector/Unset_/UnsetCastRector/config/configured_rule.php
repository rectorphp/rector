<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php72\Rector\Unset_\UnsetCastRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(UnsetCastRector::class);
};
