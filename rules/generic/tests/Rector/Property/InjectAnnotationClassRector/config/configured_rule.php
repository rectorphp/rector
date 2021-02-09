<?php

use JMS\DiExtraBundle\Annotation\Inject;
use Rector\Core\Configuration\Option;
use Rector\Generic\Rector\Property\InjectAnnotationClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(
        Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER,
        __DIR__ . '/../../../../../../symfony/tests/Rector/MethodCall/GetToConstructorInjectionRector/xml/services.xml'
    );

    $services = $containerConfigurator->services();
    $services->set(InjectAnnotationClassRector::class)
        ->call('configure', [[
            InjectAnnotationClassRector::ANNOTATION_CLASSES => [Inject::class, \DI\Annotation\Inject::class],
        ]]);
};
