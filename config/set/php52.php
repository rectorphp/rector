<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Php52\Rector\Property\VarToPublicPropertyRector::class);
    $rectorConfig->rule(\Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector::class, [
        // see https://www.php.net/manual/en/function.ldap-first-attribute.php
        new \Rector\Removing\ValueObject\RemoveFuncCallArg('ldap_first_attribute', 2),
    ]);
};
