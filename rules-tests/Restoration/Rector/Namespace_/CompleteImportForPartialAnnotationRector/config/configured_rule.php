<?php

declare(strict_types=1);

use Rector\Restoration\Rector\Namespace_\CompleteImportForPartialAnnotationRector;
use Rector\Restoration\ValueObject\CompleteImportForPartialAnnotation;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(CompleteImportForPartialAnnotationRector::class)
        ->configure([new CompleteImportForPartialAnnotation('Doctrine\ORM\Mapping', 'ORM')]);
};
