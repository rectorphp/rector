<?php

declare (strict_types=1);
namespace RectorPrefix20220609;

use Rector\Config\RectorConfig;
use Rector\Nette\Rector\Property\NetteInjectToConstructorInjectionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(NetteInjectToConstructorInjectionRector::class);
};
