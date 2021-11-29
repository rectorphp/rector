<?php

declare(strict_types=1);

use Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector;
use Rector\Renaming\ValueObject\RenameAnnotation;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenameAnnotationRector::class)
        ->configure([new RenameAnnotation('psalm-ignore', 'phpstan-ignore')]);
};
