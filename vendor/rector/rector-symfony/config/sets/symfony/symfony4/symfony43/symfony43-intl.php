<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony43\Rector\MethodCall\GetCurrencyBundleMethodCallsToIntlRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([GetCurrencyBundleMethodCallsToIntlRector::class]);
};
