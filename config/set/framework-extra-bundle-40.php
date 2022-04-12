<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\ClassMethod\RemoveServiceFromSensioRouteRector;
use Rector\Symfony\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(ReplaceSensioRouteAnnotationWithSymfonyRector::class);

    $services->set(RemoveServiceFromSensioRouteRector::class);
};
