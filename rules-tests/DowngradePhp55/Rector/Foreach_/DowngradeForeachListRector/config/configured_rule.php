<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp55\Rector\Foreach_\DowngradeForeachListRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeForeachListRector::class);
};
