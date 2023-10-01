<?php

declare (strict_types=1);
namespace RectorPrefix202310;

use RectorPrefix202310\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix202310\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->typesToSkip([SerializableInterface::class]);
};
