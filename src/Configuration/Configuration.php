<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\Exception\Configuration\InvalidConfigurationException;
use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
use Symfony\Component\Console\Input\InputInterface;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Configuration
{
    private bool $isDryRun = false;

    private bool $showProgressBar = true;

    private bool $shouldClearCache = false;

    private string $outputFormat;

    private bool $isCacheDebug = false;

    private bool $isCacheEnabled = false;

    /**
     * @var string[]
     */
    private array $fileExtensions = [];

    /**
     * @var string[]
     */
    private array $paths = [];

    private ParameterProvider $parameterProvider;

    private bool $showDiffs = true;

    private ?BootstrapConfigs $bootstrapConfigs = null;

    public function __construct(ParameterProvider $parameterProvider)
    {
        $this->isCacheEnabled = (bool) $parameterProvider->provideParameter(Option::ENABLE_CACHE);
        $this->fileExtensions = (array) $parameterProvider->provideParameter(Option::FILE_EXTENSIONS);
        $this->paths = (array) $parameterProvider->provideParameter(Option::PATHS);
        $this->parameterProvider = $parameterProvider;
    }

    /**
     * Needs to run in the start of the life cycle, since the rest of workflow uses it.
     */
    public function resolveFromInput(InputInterface $input): void
    {
        $this->isDryRun = (bool) $input->getOption(Option::OPTION_DRY_RUN);
        $this->shouldClearCache = (bool) $input->getOption(Option::OPTION_CLEAR_CACHE);

        $this->showProgressBar = $this->canShowProgressBar($input);
        $this->showDiffs = ! (bool) $input->getOption(Option::OPTION_NO_DIFFS);
        $this->isCacheDebug = (bool) $input->getOption(Option::CACHE_DEBUG);

        $this->outputFormat = (string) $input->getOption(Option::OPTION_OUTPUT_FORMAT);

        $commandLinePaths = (array) $input->getArgument(Option::SOURCE);

        // manual command line value has priority
        if ($commandLinePaths !== []) {
            $commandLinePaths = $this->correctBashSpacePaths($commandLinePaths);
            $this->paths = $commandLinePaths;
        }
    }

    /**
     * @forTests
     */
    public function setIsDryRun(bool $isDryRun): void
    {
        $this->isDryRun = $isDryRun;
    }

    public function isDryRun(): bool
    {
        return $this->isDryRun;
    }

    public function shouldShowProgressBar(): bool
    {
        if ($this->isCacheDebug) {
            return false;
        }

        return $this->showProgressBar;
    }

    public function shouldClearCache(): bool
    {
        return $this->shouldClearCache;
    }

    public function isCacheDebug(): bool
    {
        return $this->isCacheDebug;
    }

    public function isCacheEnabled(): bool
    {
        return $this->isCacheEnabled;
    }

    /**
     * @return string[]
     */
    public function getFileExtensions(): array
    {
        return $this->fileExtensions;
    }

    /**
     * @return string[]
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getOutputFormat(): string
    {
        return $this->outputFormat;
    }

    public function validateConfigParameters(): void
    {
        $symfonyContainerXmlPath = (string) $this->parameterProvider->provideParameter(
            Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER
        );
        if ($symfonyContainerXmlPath === '') {
            return;
        }

        if (file_exists($symfonyContainerXmlPath)) {
            return;
        }

        $message = sprintf(
            'Path "%s" for "$parameters->set(Option::%s, ...);" in your config was not found. Correct it',
            $symfonyContainerXmlPath,
            'SYMFONY_CONTAINER_XML_PATH_PARAMETER'
        );
        throw new InvalidConfigurationException($message);
    }

    public function shouldHideClutter(): bool
    {
        return $this->outputFormat !== ConsoleOutputFormatter::NAME;
    }

    public function shouldShowDiffs(): bool
    {
        return $this->showDiffs;
    }

    public function setBootstrapConfigs(BootstrapConfigs $bootstrapConfigs): void
    {
        $this->bootstrapConfigs = $bootstrapConfigs;
    }

    public function getMainConfigFilePath(): ?string
    {
        if ($this->bootstrapConfigs === null) {
            return null;
        }

        $mainConfigFileInfo = $this->bootstrapConfigs->getMainConfigFileInfo();
        if (! $mainConfigFileInfo instanceof SmartFileInfo) {
            return null;
        }

        return $mainConfigFileInfo->getRelativeFilePathFromCwd();
    }

    private function canShowProgressBar(InputInterface $input): bool
    {
        $noProgressBar = (bool) $input->getOption(Option::OPTION_NO_PROGRESS_BAR);
        if ($noProgressBar) {
            return false;
        }

        $optionOutputFormat = $input->getOption(Option::OPTION_OUTPUT_FORMAT);
        return $optionOutputFormat === ConsoleOutputFormatter::NAME;
    }

    /**
     * @param string[] $commandLinePaths
     * @return string[]
     */
    private function correctBashSpacePaths(array $commandLinePaths): array
    {
        // fixes bash edge-case that to merges string with space to one
        foreach ($commandLinePaths as $commandLinePath) {
            if (\str_contains($commandLinePath, ' ')) {
                $commandLinePaths = explode(' ', $commandLinePath);
            }
        }

        return $commandLinePaths;
    }
}
