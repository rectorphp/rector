<?php

declare(strict_types=1);

use Rector\Architecture\Rector\Class_\MoveRepositoryFromParentToConstructorRector;
use Rector\Architecture\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector;
use Rector\Architecture\Rector\MethodCall\ServiceLocatorToDIRector;
use Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * @see https://tomasvotruba.com/blog/2017/10/16/how-to-use-repository-with-doctrine-as-service-in-symfony/
 * @see https://tomasvotruba.com/blog/2018/04/02/rectify-turn-repositories-to-services-in-symfony/
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # order matters, this needs to be first to correctly detect parent repository
    $services->set(MoveRepositoryFromParentToConstructorRector::class);

    $services->set(ServiceLocatorToDIRector::class);

    $services->set(ReplaceParentRepositoryCallsByRepositoryPropertyRector::class);

    $services->set(RemoveRepositoryFromEntityAnnotationRector::class);

    $services->set(ReplaceParentRepositoryCallsByRepositoryPropertyRector::class);
};
