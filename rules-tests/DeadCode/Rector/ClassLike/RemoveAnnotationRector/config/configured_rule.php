<?php

declare(strict_types=1);

use Rector\DeadCode\Rector\ClassLike\RemoveAnnotationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveAnnotationRector::class)
        ->configure(['method', 'JMS\DiExtraBundle\Annotation\InjectParams']);
};
