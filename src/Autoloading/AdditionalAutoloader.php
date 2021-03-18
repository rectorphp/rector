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
    /**
     * @var FileSystemGuard
     */
    private $fileSystemGuard;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var DynamicSourceLocatorDecorator
     */
    private $dynamicSourceLocatorDecorator;

    public function __construct(
        FileSystemGuard $fileSystemGuard,
        ParameterProvider $parameterProvider,
        DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator
    ) {
        $this->fileSystemGuard = $fileSystemGuard;
        $this->parameterProvider = $parameterProvider;
        $this->dynamicSourceLocatorDecorator = $dynamicSourceLocatorDecorator;
    }

    public function autoloadWithInputAndSource(InputInterface $input): void
    {
        if ($input->hasOption(Option::OPTION_AUTOLOAD_FILE)) {
            $this->autoloadInputAutoloadFile($input);
        }

        $autoloadPaths = $this->parameterProvider->provideArrayParameter(Option::AUTOLOAD_PATHS);
        if ($autoloadPaths === []) {
            return;
        }

        $this->dynamicSourceLocatorDecorator->addPaths($autoloadPaths);
    }

    private function autoloadInputAutoloadFile(InputInterface $input): void
    {
        /** @var string|null $autoloadFile */
        $autoloadFile = $input->getOption(Option::OPTION_AUTOLOAD_FILE);
        if ($autoloadFile === null) {
            return;
        }

        $this->fileSystemGuard->ensureFileExists($autoloadFile, 'Extra autoload');
        $this->dynamicSourceLocatorDecorator->addPaths([$autoloadFile]);
    }
}
