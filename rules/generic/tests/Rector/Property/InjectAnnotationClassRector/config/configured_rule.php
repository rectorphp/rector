<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Generic\Rector\Property\InjectAnnotationClassRector::class)->call('configure', [[
        \Rector\Generic\Rector\Property\InjectAnnotationClassRector::ANNOTATION_CLASSES => [
            \JMS\DiExtraBundle\Annotation\Inject::class,
            \DI\Annotation\Inject::class,
        ],
    ]]);
};
