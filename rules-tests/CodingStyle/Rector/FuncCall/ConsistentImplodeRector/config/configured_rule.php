<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(ConsistentImplodeRector::class);
};
