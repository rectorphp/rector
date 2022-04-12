<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php56\Rector\FuncCall\PowToExpRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(PowToExpRector::class);
};
