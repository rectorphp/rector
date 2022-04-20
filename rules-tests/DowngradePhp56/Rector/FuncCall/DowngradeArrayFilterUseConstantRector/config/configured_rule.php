<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp56\Rector\FuncCall\DowngradeArrayFilterUseConstantRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeArrayFilterUseConstantRector::class);
};
