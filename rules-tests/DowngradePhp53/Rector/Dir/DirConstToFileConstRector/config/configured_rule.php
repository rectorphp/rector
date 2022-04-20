<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp53\Rector\Dir\DirConstToFileConstRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DirConstToFileConstRector::class);
};
