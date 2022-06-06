<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use RectorPrefix20220606\Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector;
use RectorPrefix20220606\Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use RectorPrefix20220606\Rector\Removing\ValueObject\RemoveFuncCallArg;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(VarToPublicPropertyRector::class);
    $rectorConfig->rule(ContinueToBreakInSwitchRector::class);
    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        // see https://www.php.net/manual/en/function.ldap-first-attribute.php
        new RemoveFuncCallArg('ldap_first_attribute', 2),
    ]);
};
