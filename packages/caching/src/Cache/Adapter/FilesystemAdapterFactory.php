<?php

declare(strict_types=1);

namespace Rector\Caching\Cache\Adapter;

use Nette\Utils\Strings;
use Rector\Core\Configuration\Option;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class FilesystemAdapterFactory
{
    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct(ParameterProvider $parameterProvider)
    {
        $this->parameterProvider = $parameterProvider;
    }

    public function create(): FilesystemAdapter
    {
        return new FilesystemAdapter(
            // unique per project
            Strings::webalize(getcwd()),
            0,
            $this->parameterProvider->provideParameter(Option::CACHE_DIR)
        );
    }
}
