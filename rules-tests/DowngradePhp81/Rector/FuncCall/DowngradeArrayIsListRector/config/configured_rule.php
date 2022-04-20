<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeArrayIsListRector::class);
};
