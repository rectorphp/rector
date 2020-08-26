<?php

declare(strict_types=1);

use Rector\Generic\ValueObject\RemovedFunctionArgument;
use Rector\Generic\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Rector\SymfonyPhpConfig\inline_value_objects;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(VarToPublicPropertyRector::class);
    $services->set(ContinueToBreakInSwitchRector::class);

    $services->set(RemoveFuncCallArgRector::class)
        ->call('configure', [[
            RemoveFuncCallArgRector::REMOVED_FUNCTION_ARGUMENTS => inline_value_objects([
                // see https://www.php.net/manual/en/function.ldap-first-attribute.php
                new RemovedFunctionArgument('ldap_first_attribute', 2),
            ]),
        ]]);
};
