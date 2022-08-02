<?php

declare (strict_types=1);
namespace Rector\Caching;

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use Rector\Core\Configuration\Option;
use RectorPrefix202208\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileSystem;
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
    public function create() : \Rector\Caching\Cache
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
            return new \Rector\Caching\Cache($fileCacheStorage);
        }
        return new \Rector\Caching\Cache(new MemoryCacheStorage());
    }
}
