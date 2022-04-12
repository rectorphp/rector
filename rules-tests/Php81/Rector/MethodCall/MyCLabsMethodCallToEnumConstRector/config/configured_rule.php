<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(MyCLabsMethodCallToEnumConstRector::class);
};
