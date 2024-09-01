<?php

declare (strict_types=1);
namespace RectorPrefix202409;

use Rector\Config\RectorConfig;
use Rector\Symfony\Configs\Rector\Closure\MergeServiceNameTypeRector;
use Rector\Symfony\Configs\Rector\Closure\ServiceArgsToServiceNamedArgRector;
use Rector\Symfony\Configs\Rector\Closure\ServiceSetStringNameToClassNameRector;
use Rector\Symfony\Configs\Rector\Closure\ServiceSettersToSettersAutodiscoveryRector;
use Rector\Symfony\Configs\Rector\Closure\ServiceTagsToDefaultsAutoconfigureRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([MergeServiceNameTypeRector::class, ServiceArgsToServiceNamedArgRector::class, ServiceSetStringNameToClassNameRector::class, ServiceSettersToSettersAutodiscoveryRector::class, ServiceTagsToDefaultsAutoconfigureRector::class]);
};
