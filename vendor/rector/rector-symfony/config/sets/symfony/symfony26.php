<?php

declare (strict_types=1);
namespace RectorPrefix20220415;

use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\MethodCall\RedirectToRouteRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    $services->set(\Rector\Symfony\Rector\MethodCall\RedirectToRouteRector::class);
};
