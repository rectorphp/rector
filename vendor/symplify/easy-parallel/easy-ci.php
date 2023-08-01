<?php

declare (strict_types=1);
namespace RectorPrefix202308;

use RectorPrefix202308\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix202308\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->typesToSkip([SerializableInterface::class]);
};
