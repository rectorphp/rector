<?php

declare (strict_types=1);
namespace Rector\Caching\Cache;

use RectorPrefix20210514\Nette\Caching\Cache;
use RectorPrefix20210514\Nette\Caching\Storages\FileStorage;
use Rector\Core\Configuration\Option;
use RectorPrefix20210514\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20210514\Symplify\SmartFileSystem\SmartFileSystem;
final class NetteCacheFactory
{
    /**
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    public function __construct(\RectorPrefix20210514\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \RectorPrefix20210514\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem)
    {
        $this->parameterProvider = $parameterProvider;
        $this->smartFileSystem = $smartFileSystem;
    }
    public function create() : \RectorPrefix20210514\Nette\Caching\Cache
    {
        $cacheDirectory = $this->parameterProvider->provideStringParameter(\Rector\Core\Configuration\Option::CACHE_DIR);
        // ensure cache directory exists
        if (!$this->smartFileSystem->exists($cacheDirectory)) {
            $this->smartFileSystem->mkdir($cacheDirectory);
        }
        $fileStorage = new \RectorPrefix20210514\Nette\Caching\Storages\FileStorage($cacheDirectory);
        // namespace is unique per project
        return new \RectorPrefix20210514\Nette\Caching\Cache($fileStorage, \getcwd());
    }
}
