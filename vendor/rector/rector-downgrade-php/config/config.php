<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('Rector\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Downgrade*/Rector', __DIR__ . '/../src/*/ValueObject', __DIR__ . '/../src/DowngradePhp80/Reflection/SimplePhpParameterReflection.php']);
};
