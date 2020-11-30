<?php

declare(strict_types=1);

use Rector\Testing\PhpConfigPrinter\SymfonyVersionFeatureGuard;
use Rector\Testing\PhpConfigPrinter\YamlFileContentProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\PhpConfigPrinter\Contract\SymfonyVersionFeatureGuardInterface;
use Symplify\PhpConfigPrinter\Contract\YamlFileContentProviderInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->public()
        ->autoconfigure();

    $services->set(SymfonyVersionFeatureGuard::class);
    $services->alias(SymfonyVersionFeatureGuardInterface::class, SymfonyVersionFeatureGuard::class);

    $services->set(YamlFileContentProvider::class);
    $services->alias(YamlFileContentProviderInterface::class, YamlFileContentProvider::class);
};
