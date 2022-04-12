<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(Php8ResourceReturnToObjectRector::class);
};
