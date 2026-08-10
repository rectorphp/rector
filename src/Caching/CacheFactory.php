<?php

declare (strict_types=1);
namespace Rector\Caching;

use RectorPrefix202608\OndraM\CiDetector\CiDetector;
use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
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
        // in CI the workspace is ephemeral and usually starts from scratch,
        // so a file cache that is never read again is only wasted IO → use faster in-memory cache
        if ((new CiDetector())->isCiDetected()) {
            return new \Rector\Caching\Cache(new MemoryCacheStorage());
        }
        $cacheDirectory = SimpleParameterProvider::provideStringParameter(Option::CACHE_DIR);
        // ensure cache directory exists
        if (!$this->fileSystem->exists($cacheDirectory)) {
            $this->fileSystem->mkdir($cacheDirectory);
        }
        $fileCacheStorage = new FileCacheStorage($cacheDirectory, $this->fileSystem);
        return new \Rector\Caching\Cache($fileCacheStorage);
    }
}
