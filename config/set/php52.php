<?php

declare(strict_types=1);

use Rector\Generic\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(VarToPublicPropertyRector::class);

    $services->set(ContinueToBreakInSwitchRector::class);

    $services->set(RemoveFuncCallArgRector::class)
        ->call('configure', [[
            RemoveFuncCallArgRector::ARGUMENT_POSITION_BY_FUNCTION_NAME => [
                'ldap_first_attribute' => [
                    # see https://www.php.net/manual/en/function.ldap-first-attribute.php
                    2,
                ],
            ],
        ]]);
};
