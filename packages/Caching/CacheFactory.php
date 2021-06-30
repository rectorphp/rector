<?php

declare (strict_types=1);
namespace Rector\Caching;

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Core\Configuration\Option;
use RectorPrefix20210630\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20210630\Symplify\SmartFileSystem\SmartFileSystem;
final class CacheFactory
{
    /**
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    public function __construct(\RectorPrefix20210630\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \RectorPrefix20210630\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem)
    {
        $this->parameterProvider = $parameterProvider;
        $this->smartFileSystem = $smartFileSystem;
    }
    public function create() : \Rector\Caching\Cache
    {
        $cacheDirectory = $this->parameterProvider->provideStringParameter(\Rector\Core\Configuration\Option::CACHE_DIR);
        // ensure cache directory exists
        if (!$this->smartFileSystem->exists($cacheDirectory)) {
            $this->smartFileSystem->mkdir($cacheDirectory);
        }
        $fileCacheStorage = new \Rector\Caching\ValueObject\Storage\FileCacheStorage($cacheDirectory, $this->smartFileSystem);
        return new \Rector\Caching\Cache($fileCacheStorage);
    }
}
