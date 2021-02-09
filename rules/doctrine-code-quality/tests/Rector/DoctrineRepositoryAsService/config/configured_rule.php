<?php

use Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;
use Rector\Doctrine\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector;
use Rector\DoctrineCodeQuality\Rector\Class_\MoveRepositoryFromParentToConstructorRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(
        # order matters, this needs to be first to correctly detect parent repository
        MoveRepositoryFromParentToConstructorRector::class
    );
    $services->set(ReplaceParentRepositoryCallsByRepositoryPropertyRector::class);
    $services->set(RemoveRepositoryFromEntityAnnotationRector::class);
};
