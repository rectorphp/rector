<?php

declare(strict_types=1);

namespace Rector\Caching;

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use Rector\Core\Configuration\Option;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileSystem;

final class CacheFactory
{
    public function __construct(
        private ParameterProvider $parameterProvider,
        private SmartFileSystem $smartFileSystem
    ) {
    }

    public function create(): Cache
    {
        $cacheDirectory = $this->parameterProvider->provideStringParameter(Option::CACHE_DIR);

        $cacheClass = FileCacheStorage::class;
        if ($this->parameterProvider->hasParameter(Option::CACHE_CLASS)) {
            $cacheClass = $this->parameterProvider->provideStringParameter(Option::CACHE_CLASS);
        }

        if ($cacheClass === FileCacheStorage::class) {
            // ensure cache directory exists
            if (! $this->smartFileSystem->exists($cacheDirectory)) {
                $this->smartFileSystem->mkdir($cacheDirectory);
            }

            $fileCacheStorage = new FileCacheStorage($cacheDirectory, $this->smartFileSystem);
            return new Cache($fileCacheStorage);
        }

        return new Cache(new MemoryCacheStorage());
    }
}
