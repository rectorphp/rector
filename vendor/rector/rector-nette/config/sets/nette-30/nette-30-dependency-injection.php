<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Nette\Rector\MethodCall\BuilderExpandToHelperExpandRector;
use Rector\Nette\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Nette\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\MethodCall\BuilderExpandToHelperExpandRector::class);
};
