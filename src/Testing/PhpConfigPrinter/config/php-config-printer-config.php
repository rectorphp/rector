<?php

declare(strict_types=1);

use Migrify\PhpConfigPrinter\Contract\SymfonyVersionFeatureGuardInterface;
use Migrify\PhpConfigPrinter\Contract\YamlFileContentProviderInterface;
use Rector\Core\Testing\PhpConfigPrinter\SymfonyVersionFeatureGuard;
use Rector\Core\Testing\PhpConfigPrinter\YamlFileContentProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire();

    $services->set(SymfonyVersionFeatureGuard::class);
    $services->alias(SymfonyVersionFeatureGuardInterface::class, SymfonyVersionFeatureGuard::class);

    $services->set(YamlFileContentProvider::class);
    $services->alias(YamlFileContentProviderInterface::class, YamlFileContentProvider::class);
};
