<?php

declare (strict_types=1);
namespace RectorPrefix20220430;

use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\MethodCall\RedirectToRouteRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Symfony\Rector\MethodCall\RedirectToRouteRector::class);
};
