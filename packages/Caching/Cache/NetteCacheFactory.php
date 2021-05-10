<?php

declare(strict_types=1);

namespace Rector\Caching\Cache;

use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;
use Rector\Core\Configuration\Option;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileSystem;

final class NetteCacheFactory
{
    public function __construct(
        private ParameterProvider $parameterProvider,
        private SmartFileSystem $smartFileSystem
    ) {
    }

    public function create(): Cache
    {
        $cacheDirectory = $this->parameterProvider->provideStringParameter(Option::CACHE_DIR);

        // ensure cache directory exists
        if (! $this->smartFileSystem->exists($cacheDirectory)) {
            $this->smartFileSystem->mkdir($cacheDirectory);
        }

        $fileStorage = new FileStorage($cacheDirectory);

        // namespace is unique per project
        return new Cache($fileStorage, getcwd());
    }
}
