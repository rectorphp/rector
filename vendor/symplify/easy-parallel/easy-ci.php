<?php

declare (strict_types=1);
namespace RectorPrefix202309;

use RectorPrefix202309\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix202309\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->typesToSkip([SerializableInterface::class]);
};
