<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Caching;

use RectorPrefix20220606\Rector\Caching\ValueObject\Storage\FileCacheStorage;
use RectorPrefix20220606\Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use RectorPrefix20220606\Rector\Core\Configuration\Option;
use RectorPrefix20220606\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20220606\Symplify\SmartFileSystem\SmartFileSystem;
final class CacheFactory
{
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    public function __construct(ParameterProvider $parameterProvider, SmartFileSystem $smartFileSystem)
    {
        $this->parameterProvider = $parameterProvider;
        $this->smartFileSystem = $smartFileSystem;
    }
    public function create() : Cache
    {
        $cacheDirectory = $this->parameterProvider->provideStringParameter(Option::CACHE_DIR);
        $cacheClass = FileCacheStorage::class;
        if ($this->parameterProvider->hasParameter(Option::CACHE_CLASS)) {
            $cacheClass = $this->parameterProvider->provideStringParameter(Option::CACHE_CLASS);
        }
        if ($cacheClass === FileCacheStorage::class) {
            // ensure cache directory exists
            if (!$this->smartFileSystem->exists($cacheDirectory)) {
                $this->smartFileSystem->mkdir($cacheDirectory);
            }
            $fileCacheStorage = new FileCacheStorage($cacheDirectory, $this->smartFileSystem);
            return new Cache($fileCacheStorage);
        }
        return new Cache(new MemoryCacheStorage());
    }
}
