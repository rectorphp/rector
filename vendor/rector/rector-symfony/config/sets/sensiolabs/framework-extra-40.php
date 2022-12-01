<?php

declare (strict_types=1);
namespace RectorPrefix202212;

use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\ClassMethod\RemoveServiceFromSensioRouteRector;
use Rector\Symfony\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ReplaceSensioRouteAnnotationWithSymfonyRector::class);
    $rectorConfig->rule(RemoveServiceFromSensioRouteRector::class);
};
