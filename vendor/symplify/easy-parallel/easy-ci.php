<?php

declare (strict_types=1);
namespace RectorPrefix202303;

use RectorPrefix202303\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix202303\Symplify\EasyCI\Config\EasyCIConfig;
return static function (EasyCIConfig $easyCIConfig) : void {
    $easyCIConfig->typesToSkip([SerializableInterface::class]);
};
