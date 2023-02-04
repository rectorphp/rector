<?php

declare (strict_types=1);
namespace RectorPrefix202302;

use RectorPrefix202302\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix202302\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->typesToSkip([SerializableInterface::class]);
};
