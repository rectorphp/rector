<?php

declare (strict_types=1);
namespace RectorPrefix202306;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony26\Rector\MethodCall\RedirectToRouteRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../../../../../../config/config.php');
    $rectorConfig->rule(RedirectToRouteRector::class);
};
