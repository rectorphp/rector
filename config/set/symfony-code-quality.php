<?php

declare(strict_types=1);

use Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector;
use Rector\Symfony\Rector\Class_\MakeCommandLazyRector;
use Rector\SymfonyCodeQuality\Rector\Attribute\ExtractAttributeRouteNameConstantsRector;
use Rector\SymfonyCodeQuality\Rector\Class_\EventListenerToEventSubscriberRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ResponseStatusCodeRector::class);
    $services->set(MakeCommandLazyRector::class);
    $services->set(EventListenerToEventSubscriberRector::class);
    $services->set(ExtractAttributeRouteNameConstantsRector::class);
};
