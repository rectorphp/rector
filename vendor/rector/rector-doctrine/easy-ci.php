<?php

declare (strict_types=1);
namespace RectorPrefix202401;

use RectorPrefix202401\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->paths([__DIR__ . '/config', __DIR__ . '/src', __DIR__ . '/rules']);
    $easyCIConfig->typesToSkip([\Rector\Contract\Rector\RectorInterface::class]);
};
