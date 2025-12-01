<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony61\Rector\Class_\MagicClosureTwigExtensionToNativeMethodsRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(MagicClosureTwigExtensionToNativeMethodsRector::class);
};
