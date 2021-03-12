<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\SymfonyCodeQuality\Rector\Class_\EventListenerToEventSubscriberRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    // wtf: all test have to be in single file due to autoloading race-condigition and container creating issue of fixture
    $parameters->set(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, __DIR__ . '/listener_services.xml');

    $services = $containerConfigurator->services();
    $services->set(EventListenerToEventSubscriberRector::class);
};
