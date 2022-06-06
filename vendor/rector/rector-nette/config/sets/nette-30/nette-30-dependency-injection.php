<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Nette\Rector\MethodCall\BuilderExpandToHelperExpandRector;
use RectorPrefix20220606\Rector\Nette\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(SetClassWithArgumentToSetFactoryRector::class);
    $rectorConfig->rule(BuilderExpandToHelperExpandRector::class);
};
