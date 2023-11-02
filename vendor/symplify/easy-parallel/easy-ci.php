<?php

declare (strict_types=1);
namespace RectorPrefix202311;

use RectorPrefix202311\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix202311\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->typesToSkip([SerializableInterface::class]);
};
