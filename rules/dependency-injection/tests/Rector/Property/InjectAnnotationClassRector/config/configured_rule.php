<?php

use Rector\Core\Configuration\Option;
use Rector\DependencyInjection\Rector\Property\InjectAnnotationClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(
        Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER,
        __DIR__ . '/../../../../../../symfony/tests/Rector/MethodCall/GetToConstructorInjectionRector/xml/services.xml'
    );

    $services = $containerConfigurator->services();
    $services->set(InjectAnnotationClassRector::class);
};
