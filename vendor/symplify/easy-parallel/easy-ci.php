<?php

declare (strict_types=1);
namespace RectorPrefix202307;

use RectorPrefix202307\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix202307\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->typesToSkip([SerializableInterface::class]);
};
