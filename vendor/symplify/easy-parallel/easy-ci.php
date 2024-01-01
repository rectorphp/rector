<?php

declare (strict_types=1);
namespace RectorPrefix202401;

use RectorPrefix202401\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix202401\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->typesToSkip([SerializableInterface::class]);
};
