<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(TernaryToNullCoalescingRector::class);

    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PARALLEL, false);

    // to invalidate cache and change file everytime
    $parameters->set(Option::CACHE_CLASS, MemoryCacheStorage::class);
};
