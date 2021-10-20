<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Php52\Rector\Property\VarToPublicPropertyRector::class);
    $services->set(\Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector::class);
    $services->set(\Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector::class)->call('configure', [[\Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector::REMOVED_FUNCTION_ARGUMENTS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
        // see https://www.php.net/manual/en/function.ldap-first-attribute.php
        new \Rector\Removing\ValueObject\RemoveFuncCallArg('ldap_first_attribute', 2),
    ])]]);
};
