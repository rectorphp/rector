<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php70\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(BreakNotInLoopOrSwitchToReturnRector::class);
};
