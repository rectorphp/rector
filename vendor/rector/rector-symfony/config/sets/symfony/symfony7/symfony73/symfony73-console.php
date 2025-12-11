<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony73\Rector\Class_\CommandDefaultNameAndDescriptionToAsCommandAttributeRector;
use Rector\Symfony\Symfony73\Rector\Class_\CommandHelpToAttributeRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([CommandHelpToAttributeRector::class, CommandDefaultNameAndDescriptionToAsCommandAttributeRector::class]);
};
