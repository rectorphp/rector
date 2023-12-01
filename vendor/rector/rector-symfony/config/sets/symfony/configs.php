<?php

declare (strict_types=1);
namespace RectorPrefix202312;

use Rector\Config\RectorConfig;
use Rector\Symfony\Configs\Rector\Closure\ServiceArgsToServiceNamedArgRector;
use Rector\Symfony\Configs\Rector\Closure\ServiceSetStringNameToClassNameRector;
use Rector\Symfony\Configs\Rector\Closure\ServiceSettersToSettersAutodiscoveryRector;
use Rector\Symfony\Configs\Rector\Closure\ServicesSetNameToSetTypeRector;
use Rector\Symfony\Configs\Rector\Closure\ServiceTagsToDefaultsAutoconfigureRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([ServiceArgsToServiceNamedArgRector::class, ServiceSetStringNameToClassNameRector::class, ServiceSettersToSettersAutodiscoveryRector::class, ServicesSetNameToSetTypeRector::class, ServiceTagsToDefaultsAutoconfigureRector::class]);
};
