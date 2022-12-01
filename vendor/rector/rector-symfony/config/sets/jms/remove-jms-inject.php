<?php

declare (strict_types=1);
namespace RectorPrefix202212;

use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\Property\JMSInjectPropertyToConstructorInjectionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(JMSInjectPropertyToConstructorInjectionRector::class);
};
