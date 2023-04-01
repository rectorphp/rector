<?php

declare (strict_types=1);
namespace RectorPrefix202304;

use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\MethodCall\RedirectToRouteRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(RedirectToRouteRector::class);
};
