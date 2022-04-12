<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php53\Rector\Ternary\TernaryToElvisRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(TernaryToElvisRector::class);
};
