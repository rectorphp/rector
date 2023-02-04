<?php

declare (strict_types=1);
namespace RectorPrefix202302;

use Rector\Core\Contract\Rector\RectorInterface;
use RectorPrefix202302\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->paths([__DIR__ . '/config', __DIR__ . '/src']);
    $easyCIConfig->typesToSkip([RectorInterface::class, \Rector\Set\Contract\SetListInterface::class]);
};
