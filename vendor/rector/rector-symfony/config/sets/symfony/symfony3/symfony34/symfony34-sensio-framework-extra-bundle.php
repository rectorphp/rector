<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony34\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector;
use Rector\Symfony\Symfony34\Rector\ClassMethod\RemoveServiceFromSensioRouteRector;
use Rector\Symfony\Symfony34\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([MergeMethodAnnotationToRouteAnnotationRector::class, RemoveServiceFromSensioRouteRector::class, ReplaceSensioRouteAnnotationWithSymfonyRector::class]);
};
