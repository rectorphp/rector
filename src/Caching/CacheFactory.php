<?php

declare (strict_types=1);
namespace Rector\Caching;

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use RectorPrefix202608\Symfony\Component\Filesystem\Filesystem;
final class CacheFactory
{
    /**
     * @readonly
     */
    private Filesystem $fileSystem;
    public function __construct(Filesystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }
    /**
     * @api config factory
     */
    public function create(): \Rector\Caching\Cache
    {
        $cacheDirectory = SimpleParameterProvider::provideStringParameter(Option::CACHE_DIR);
        // ensure cache directory exists
        if (!$this->fileSystem->exists($cacheDirectory)) {
            $this->fileSystem->mkdir($cacheDirectory);
        }
        $fileCacheStorage = new FileCacheStorage($cacheDirectory, $this->fileSystem);
        return new \Rector\Caching\Cache($fileCacheStorage);
    }
}
