<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(PrivatizeLocalGetterToPropertyRector::class);
};
