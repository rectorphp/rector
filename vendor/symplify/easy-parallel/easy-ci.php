<?php

declare (strict_types=1);
namespace RectorPrefix202312;

use RectorPrefix202312\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix202312\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->typesToSkip([SerializableInterface::class]);
};
