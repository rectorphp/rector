<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\Class_\RemoveTraitUseRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RemoveTraitUseRector::class, [
        # see https://laravel.com/docs/5.3/upgrade
        'Illuminate\\Foundation\\Auth\\Access\\AuthorizesResources',
    ]);
};
