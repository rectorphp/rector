<?php

declare(strict_types=1);

use Rector\Symfony5\Rector\Class_\LogoutHandlerToLogoutEventSubscriberRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(LogoutHandlerToLogoutEventSubscriberRector::class);
};
