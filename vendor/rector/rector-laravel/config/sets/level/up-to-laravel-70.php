<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Laravel\Set\LaravelLevelSetList;
use RectorPrefix20220606\Rector\Laravel\Set\LaravelSetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([LaravelSetList::LARAVEL_70, LaravelLevelSetList::UP_TO_LARAVEL_60]);
};
