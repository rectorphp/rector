<?php

declare (strict_types=1);
namespace RectorPrefix202407;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony26\Rector\MethodCall\RedirectToRouteRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(RedirectToRouteRector::class);
};
