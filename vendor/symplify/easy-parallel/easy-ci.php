<?php

declare (strict_types=1);
namespace RectorPrefix202304;

use RectorPrefix202304\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix202304\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->typesToSkip([SerializableInterface::class]);
};
