<?php

declare(strict_types=1);

use PHPStan\Dependency\DependencyResolver;
use PHPStan\File\FileHelper;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Rector\Caching\Cache\Adapter\FilesystemAdapterFactory;
use Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('is_cache_enabled', false);

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->public();

    $services->load('Rector\Caching\\', __DIR__ . '/../src');

    $services->set(DependencyResolver::class)
        ->factory([ref(PHPStanServicesFactory::class), 'createDependencyResolver']);

    $services->set(FileHelper::class)
        ->factory([ref(PHPStanServicesFactory::class), 'createFileHelper']);

    $services->set(Psr16Cache::class);

    $services->alias(CacheInterface::class, Psr16Cache::class);

    $services->set(FilesystemAdapter::class)
        ->factory([ref(FilesystemAdapterFactory::class), 'create']);

    $services->set(TagAwareAdapter::class)
        ->arg('$itemsPool', ref(FilesystemAdapter::class));

    $services->alias(CacheItemPoolInterface::class, FilesystemAdapter::class);

    $services->alias(TagAwareAdapterInterface::class, TagAwareAdapter::class);
};
