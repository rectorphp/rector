<?php

declare (strict_types=1);
namespace RectorPrefix202401;

use Rector\Config\RectorConfig;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
return RectorConfig::configure()->withRules([PrivatizeLocalGetterToPropertyRector::class, PrivatizeFinalClassPropertyRector::class, PrivatizeFinalClassMethodRector::class]);
