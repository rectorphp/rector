<?php

declare(strict_types=1);

namespace Rector\Caching\Cache;

use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;
use Nette\Utils\Strings;
use Rector\Core\Configuration\Option;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class NetteCacheFactory
{
    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct(ParameterProvider $parameterProvider)
    {
        $this->parameterProvider = $parameterProvider;
    }

    public function create(): Cache
    {
        $cacheDirectory = $this->parameterProvider->provideStringParameter(Option::CACHE_DIR);
        $fileStorage = new FileStorage($cacheDirectory);

        // namespace is unique per project
        $namespace = Strings::webalize(getcwd());

        return new Cache($fileStorage, $namespace);
    }
}
