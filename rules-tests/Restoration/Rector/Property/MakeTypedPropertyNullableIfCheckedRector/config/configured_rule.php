<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Restoration\Rector\Property\MakeTypedPropertyNullableIfCheckedRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(MakeTypedPropertyNullableIfCheckedRector::class);
};
