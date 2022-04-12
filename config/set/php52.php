<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(VarToPublicPropertyRector::class);
    $services->set(ContinueToBreakInSwitchRector::class);

    $services->set(RemoveFuncCallArgRector::class)
        ->configure([
            // see https://www.php.net/manual/en/function.ldap-first-attribute.php
            new RemoveFuncCallArg('ldap_first_attribute', 2),
        ]);
};
