<?php

declare(strict_types=1);

use Rector\Generic\Rector\Property\InjectAnnotationClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(InjectAnnotationClassRector::class)
        ->call('configure', [[
            InjectAnnotationClassRector::ANNOTATION_CLASSES => ['DI\Annotation\Inject'],
        ]]);
};
