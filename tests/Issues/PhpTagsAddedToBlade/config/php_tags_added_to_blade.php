<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use Rector\Core\Configuration\Option;
use Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(TernaryToNullCoalescingRector::class);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PARALLEL, false);

    // to invalidate cache and change file everytime
    $parameters->set(Option::CACHE_CLASS, MemoryCacheStorage::class);
};
