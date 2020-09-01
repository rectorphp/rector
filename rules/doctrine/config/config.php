<?php

declare(strict_types=1);

use Rector\Doctrine\Contract\Mapper\DoctrineEntityAndRepositoryMapperInterface;
use Rector\Doctrine\Mapper\DefaultDoctrineEntityAndRepositoryMapper;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Rector\Doctrine\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/Rector']);

    $services->alias(
        DoctrineEntityAndRepositoryMapperInterface::class,
        DefaultDoctrineEntityAndRepositoryMapper::class
    );
};
