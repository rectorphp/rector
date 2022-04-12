<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php52\Rector\Property\VarToPublicPropertyRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(VarToPublicPropertyRector::class);
};
