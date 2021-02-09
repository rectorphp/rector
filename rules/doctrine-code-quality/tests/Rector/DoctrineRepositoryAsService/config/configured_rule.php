<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(
        # order matters, this needs to be first to correctly detect parent repository
        \Rector\DoctrineCodeQuality\Rector\Class_\MoveRepositoryFromParentToConstructorRector::class
    );
    $services->set(\Rector\Doctrine\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector::class);
    $services->set(\Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector::class);
};
