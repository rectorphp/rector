<?php

declare(strict_types=1);

use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Removing\ValueObject\RemoveFuncCallArg;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveFuncCallArgRector::class)
        ->configure([new RemoveFuncCallArg('ldap_first_attribute', 2)]);
};
