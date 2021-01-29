<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

use Jean85\PrettyVersions;
use Rector\ChangesReporting\Output\CheckstyleOutputFormatter;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\Core\Exception\Configuration\InvalidConfigurationException;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Symfony\Component\Console\Input\InputInterface;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Configuration
{
    /**
     * @var bool
     */
    private $isDryRun = false;

    /**
     * @var bool
     */
    private $showProgressBar = true;

    /**
     * @var bool
     */
    private $areAnyPhpRectorsLoaded = false;

    /**
     * @var bool
     */
    private $mustMatchGitDiff = false;

    /**
     * @var bool
     */
    private $shouldClearCache = false;

    /**
     * @var string
     */
    private $outputFormat;

    /**
     * @var bool
     */
    private $isCacheDebug = false;

    /**
     * @var bool
     */
    private $isCacheEnabled = false;

    /**
     * @var SmartFileInfo[]
     */
    private $fileInfos = [];

    /**
     * @var string[]
     */
    private $fileExtensions = [];

    /**
     * @var string[]
     */
    private $paths = [];

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var string|null
     */
    private $outputFile;

    /**
     * @var SmartFileInfo|null
     */
    private $configFileInfo;

    /**
     * @var bool
     */
    private $showDiffs = true;

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
        $this->mustMatchGitDiff = (bool) $input->getOption(Option::MATCH_GIT_DIFF);
        $this->showProgressBar = $this->canShowProgressBar($input);
        $this->showDiffs = ! (bool) $input->getOption(Option::OPTION_NO_DIFFS);
        $this->isCacheDebug = (bool) $input->getOption(Option::CACHE_DEBUG);

        /** @var string|null $outputFileOption */
        $outputFileOption = $input->getOption(Option::OPTION_OUTPUT_FILE);
        $this->outputFile = $this->sanitizeOutputFileValue($outputFileOption);

        $this->outputFormat = (string) $input->getOption(Option::OPTION_OUTPUT_FORMAT);

        $commandLinePaths = (array) $input->getArgument(Option::SOURCE);
        // manual command line value has priority
        if ($commandLinePaths !== []) {
            $this->paths = $commandLinePaths;
        }
    }

    /**
     * @api
     */
    public function setFirstResolverConfigFileInfo(SmartFileInfo $firstResolvedConfigFileInfo): void
    {
        $this->configFileInfo = $firstResolvedConfigFileInfo;
    }

    public function getConfigFilePath(): ?string
    {
        if ($this->configFileInfo === null) {
            return null;
        }

        return $this->configFileInfo->getRealPath();
    }

    public function getPrettyVersion(): string
    {
        $version = PrettyVersions::getVersion('rector/rector');
        return $version->getPrettyVersion();
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

    public function areAnyPhpRectorsLoaded(): bool
    {
        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return true;
        }

        return $this->areAnyPhpRectorsLoaded;
    }

    public function setAreAnyPhpRectorsLoaded(bool $areAnyPhpRectorsLoaded): void
    {
        $this->areAnyPhpRectorsLoaded = $areAnyPhpRectorsLoaded;
    }

    public function mustMatchGitDiff(): bool
    {
        return $this->mustMatchGitDiff;
    }

    public function getOutputFile(): ?string
    {
        return $this->outputFile;
    }

    /**
     * @param SmartFileInfo[] $fileInfos
     */
    public function setFileInfos(array $fileInfos): void
    {
        $this->fileInfos = $fileInfos;
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getFileInfos(): array
    {
        return $this->fileInfos;
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
        if ($this->outputFormat === JsonOutputFormatter::NAME) {
            return true;
        }
        return $this->outputFormat === CheckstyleOutputFormatter::NAME;
    }

    public function shouldShowDiffs(): bool
    {
        return $this->showDiffs;
    }

    private function canShowProgressBar(InputInterface $input): bool
    {
        $noProgressBar = (bool) $input->getOption(Option::OPTION_NO_PROGRESS_BAR);
        if ($noProgressBar) {
            return false;
        }
        $optionOutputFormat = $input->getOption(Option::OPTION_OUTPUT_FORMAT);
        if ($optionOutputFormat === JsonOutputFormatter::NAME) {
            return false;
        }
        return $input->getOption(Option::OPTION_OUTPUT_FORMAT) !== CheckstyleOutputFormatter::NAME;
    }

    private function sanitizeOutputFileValue(?string $outputFileOption): ?string
    {
        if ($outputFileOption === '') {
            return null;
        }

        return $outputFileOption;
    }
}
