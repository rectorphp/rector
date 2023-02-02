<?php

declare (strict_types=1);
namespace RectorPrefix202302;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Set\Contract\SetListInterface;
use RectorPrefix202302\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->typesToSkip([RectorInterface::class, SetListInterface::class]);
};
