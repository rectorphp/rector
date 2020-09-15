<?php

declare(strict_types=1);

use Rector\PSR4\Composer\PSR4NamespaceMatcher;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Rector\PSR4\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/Rector']);

    $services->alias(PSR4AutoloadNamespaceMatcherInterface::class, PSR4NamespaceMatcher::class);
};
