<?php

declare (strict_types=1);
namespace RectorPrefix202306;

use RectorPrefix202306\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix202306\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->typesToSkip([SerializableInterface::class]);
};
