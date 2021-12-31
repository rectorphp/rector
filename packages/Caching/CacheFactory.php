<?php

declare (strict_types=1);
namespace Rector\Caching;

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use Rector\Core\Configuration\Option;
use RectorPrefix20211231\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20211231\Symplify\SmartFileSystem\SmartFileSystem;
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
    public function __construct(\RectorPrefix20211231\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \RectorPrefix20211231\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem)
    {
        $this->parameterProvider = $parameterProvider;
        $this->smartFileSystem = $smartFileSystem;
    }
    public function create() : \Rector\Caching\Cache
    {
        $cacheDirectory = $this->parameterProvider->provideStringParameter(\Rector\Core\Configuration\Option::CACHE_DIR);
        $cacheClass = \Rector\Caching\ValueObject\Storage\FileCacheStorage::class;
        if ($this->parameterProvider->hasParameter(\Rector\Core\Configuration\Option::CACHE_CLASS)) {
            $cacheClass = $this->parameterProvider->provideStringParameter(\Rector\Core\Configuration\Option::CACHE_CLASS);
        }
        if ($cacheClass === \Rector\Caching\ValueObject\Storage\FileCacheStorage::class) {
            // ensure cache directory exists
            if (!$this->smartFileSystem->exists($cacheDirectory)) {
                $this->smartFileSystem->mkdir($cacheDirectory);
            }
            $fileCacheStorage = new \Rector\Caching\ValueObject\Storage\FileCacheStorage($cacheDirectory, $this->smartFileSystem);
            return new \Rector\Caching\Cache($fileCacheStorage);
        }
        return new \Rector\Caching\Cache(new \Rector\Caching\ValueObject\Storage\MemoryCacheStorage());
    }
}
