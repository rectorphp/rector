<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony73\Rector\Class_\GetFiltersToAsTwigFilterAttributeRector;
use Rector\Symfony\Symfony73\Rector\Class_\GetFunctionsToAsTwigFunctionAttributeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([GetFiltersToAsTwigFilterAttributeRector::class, GetFunctionsToAsTwigFunctionAttributeRector::class]);
};
