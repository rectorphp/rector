<?php

declare (strict_types=1);
namespace RectorPrefix202305;

use RectorPrefix202305\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix202305\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->typesToSkip([SerializableInterface::class]);
};
