<?php

declare (strict_types=1);
namespace RectorPrefix202505;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony73\Rector\Class_\GetFiltersToAsTwigFilterAttributeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([GetFiltersToAsTwigFilterAttributeRector::class]);
};
