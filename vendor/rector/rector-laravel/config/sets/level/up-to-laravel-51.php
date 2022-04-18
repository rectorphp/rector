<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Laravel\Set\LaravelSetList;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(\Rector\Laravel\Set\LaravelSetList::LARAVEL_50);
    $rectorConfig->import(\Rector\Laravel\Set\LaravelSetList::LARAVEL_51);
};
