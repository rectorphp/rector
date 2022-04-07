<?php

declare(strict_types=1);

use Rector\Core\ValueObject\Visibility;
use Rector\Visibility\Rector\ClassMethod\ExplicitPublicClassMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ExplicitPublicClassMethodRector::class);
};

