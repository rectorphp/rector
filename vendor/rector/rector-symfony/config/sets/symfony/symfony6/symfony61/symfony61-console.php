<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony61\Rector\Class_\CommandConfigureToAttributeRector;
use Rector\Symfony\Symfony61\Rector\Class_\CommandPropertyToAttributeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([CommandConfigureToAttributeRector::class, CommandPropertyToAttributeRector::class]);
};
