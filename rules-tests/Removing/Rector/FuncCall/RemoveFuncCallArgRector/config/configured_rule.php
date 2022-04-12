<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RemoveFuncCallArgRector::class)
        ->configure([new RemoveFuncCallArg('ldap_first_attribute', 2)]);
};
