<?php

declare (strict_types=1);
namespace Rector\Caching;

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\SimpleParameterProvider;
use RectorPrefix202308\Symfony\Component\Filesystem\Filesystem;
final class CacheFactory
{
    /**
     * @readonly
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $fileSystem;
    public function __construct(Filesystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }
    /**
     * @api config factory
     */
    public function create() : \Rector\Caching\Cache
    {
        $cacheDirectory = SimpleParameterProvider::provideStringParameter(Option::CACHE_DIR);
        $cacheClass = FileCacheStorage::class;
        if (SimpleParameterProvider::hasParameter(Option::CACHE_CLASS)) {
            $cacheClass = SimpleParameterProvider::provideStringParameter(Option::CACHE_CLASS);
        }
        if ($cacheClass === FileCacheStorage::class) {
            // ensure cache directory exists
            if (!$this->fileSystem->exists($cacheDirectory)) {
                $this->fileSystem->mkdir($cacheDirectory);
            }
            $fileCacheStorage = new FileCacheStorage($cacheDirectory, $this->fileSystem);
            return new \Rector\Caching\Cache($fileCacheStorage);
        }
        return new \Rector\Caching\Cache(new MemoryCacheStorage());
    }
}
