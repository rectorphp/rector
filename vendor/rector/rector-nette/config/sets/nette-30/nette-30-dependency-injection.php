<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Nette\Rector\MethodCall\BuilderExpandToHelperExpandRector;
use Rector\Nette\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(SetClassWithArgumentToSetFactoryRector::class);
    $rectorConfig->rule(BuilderExpandToHelperExpandRector::class);
};
