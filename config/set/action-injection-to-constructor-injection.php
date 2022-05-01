<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\DependencyInjection\Rector\Class_\ActionInjectionToConstructorInjectionRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\DependencyInjection\Rector\Class_\ActionInjectionToConstructorInjectionRector::class);
};
