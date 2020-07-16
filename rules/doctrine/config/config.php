<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Rector\Doctrine\Contract\Mapper\DoctrineEntityAndRepositoryMapperInterface;
use Rector\Doctrine\Mapper\DefaultDoctrineEntityAndRepositoryMapper;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Rector\Doctrine\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/Rector/**/*Rector.php']);

    $services->alias(DoctrineEntityAndRepositoryMapperInterface::class, DefaultDoctrineEntityAndRepositoryMapper::class);
};
