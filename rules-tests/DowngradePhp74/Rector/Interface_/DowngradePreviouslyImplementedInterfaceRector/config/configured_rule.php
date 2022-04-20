<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp74\Rector\Interface_\DowngradePreviouslyImplementedInterfaceRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradePreviouslyImplementedInterfaceRector::class);
};
