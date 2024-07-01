<?php

declare (strict_types=1);
namespace RectorPrefix202407;

use Rector\Config\RectorConfig;
use Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([VarToPublicPropertyRector::class, ContinueToBreakInSwitchRector::class]);
    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        // see https://www.php.net/manual/en/function.ldap-first-attribute.php
        new RemoveFuncCallArg('ldap_first_attribute', 2),
    ]);
};
