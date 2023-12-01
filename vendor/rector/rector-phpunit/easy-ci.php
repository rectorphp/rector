<?php

declare (strict_types=1);
namespace RectorPrefix202312;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Set\Contract\SetListInterface;
use RectorPrefix202312\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->paths([__DIR__ . '/config', __DIR__ . '/src', __DIR__ . '/rules']);
    $easyCIConfig->typesToSkip([RectorInterface::class, SetListInterface::class]);
};
