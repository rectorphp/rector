<?php

declare(strict_types=1);

use Rector\Architecture\Rector\Class_\MoveRepositoryFromParentToConstructorRector;
use Rector\Architecture\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector;
use Rector\Architecture\Rector\MethodCall\ServiceLocatorToDIRector;
use Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # order matters, this needs to be first to correctly detect parent repository
    $services->set(MoveRepositoryFromParentToConstructorRector::class);

    $services->set(ServiceLocatorToDIRector::class);

    $services->set(ReplaceParentRepositoryCallsByRepositoryPropertyRector::class);

    $services->set(RemoveRepositoryFromEntityAnnotationRector::class);
};
