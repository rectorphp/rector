<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Php52\Rector\Property\VarToPublicPropertyRector::class);
    $services->set(\Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector::class);
    $services->set(\Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector::class)->configure([
        // see https://www.php.net/manual/en/function.ldap-first-attribute.php
        new \Rector\Removing\ValueObject\RemoveFuncCallArg('ldap_first_attribute', 2),
    ]);
};
