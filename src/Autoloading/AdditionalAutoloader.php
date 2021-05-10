<?php

declare(strict_types=1);

namespace Rector\Core\Autoloading;

use Rector\Core\Configuration\Option;
use Rector\Core\StaticReflection\DynamicSourceLocatorDecorator;
use Symfony\Component\Console\Input\InputInterface;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\FileSystemGuard;

/**
 * Should it pass autoload files/directories to PHPStan analyzer?
 */
final class AdditionalAutoloader
{
    public function __construct(
        private FileSystemGuard $fileSystemGuard,
        private ParameterProvider $parameterProvider,
        private DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator
    ) {
    }

    public function autoloadInput(InputInterface $input): void
    {
        if (! $input->hasOption(Option::OPTION_AUTOLOAD_FILE)) {
            return;
        }

        /** @var string|null $autoloadFile */
        $autoloadFile = $input->getOption(Option::OPTION_AUTOLOAD_FILE);
        if ($autoloadFile === null) {
            return;
        }

        $this->fileSystemGuard->ensureFileExists($autoloadFile, 'Extra autoload');
        require_once $autoloadFile;
    }

    public function autoloadPaths(): void
    {
        $autoloadPaths = $this->parameterProvider->provideArrayParameter(Option::AUTOLOAD_PATHS);
        if ($autoloadPaths === []) {
            return;
        }

        $this->dynamicSourceLocatorDecorator->addPaths($autoloadPaths);
    }
}
