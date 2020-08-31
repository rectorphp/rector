<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

use Jean85\PrettyVersions;
use OndraM\CiDetector\CiDetector;
use Rector\ChangesReporting\Output\CheckstyleOutputFormatter;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\Core\Exception\Configuration\InvalidConfigurationException;
use Rector\Core\Testing\PHPUnit\StaticPHPUnitEnvironment;
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
     * @var string
     */
    private $outputFile;

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
     * @var CiDetector
     */
    private $ciDetector;

    /**
     * @var OnlyRuleResolver
     */
    private $onlyRuleResolver;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var SmartFileInfo|null
     */
    private $configFileInfo;

    /**
     * @var string|null
     */
    private $onlyRector;

    /**
     * @param string[] $fileExtensions
     * @param string[] $paths
     */
    public function __construct(
        CiDetector $ciDetector,
        bool $isCacheEnabled,
        array $fileExtensions,
        array $paths,
        OnlyRuleResolver $onlyRuleResolver,
        ParameterProvider $parameterProvider
    ) {
        $this->ciDetector = $ciDetector;
        $this->isCacheEnabled = $isCacheEnabled;
        $this->fileExtensions = $fileExtensions;
        $this->paths = $paths;
        $this->onlyRuleResolver = $onlyRuleResolver;
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
        $this->isCacheDebug = (bool) $input->getOption(Option::CACHE_DEBUG);

        $outputFileOption = $input->getOption(Option::OPTION_OUTPUT_FILE);
        $this->outputFile = $outputFileOption ? (string) $outputFileOption : null;

        $this->outputFormat = (string) $input->getOption(Option::OPTION_OUTPUT_FORMAT);

        /** @var string|null $onlyRector */
        $onlyRector = $input->getOption(Option::OPTION_ONLY);
        if ($onlyRector !== null) {
            $this->setOnlyRector($onlyRector);
        }

        $commandLinePaths = (array) $input->getArgument(Option::SOURCE);
        // manual command line value has priority
        if (count($commandLinePaths) > 0) {
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

    public function showProgressBar(): bool
    {
        if ($this->ciDetector->isCiDetected()) {
            return false;
        }

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

    public function getOnlyRector(): ?string
    {
        return $this->onlyRector;
    }

    public function getOutputFile(): string
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
            'Path "%s" for "parameters > %s" in your config was not found. Correct it',
            $symfonyContainerXmlPath,
            Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER
        );
        throw new InvalidConfigurationException($message);
    }

    private function canShowProgressBar(InputInterface $input): bool
    {
        $noProgressBar = (bool) $input->getOption(Option::OPTION_NO_PROGRESS_BAR);
        if ($noProgressBar) {
            return false;
        }

        if ($input->getOption(Option::OPTION_OUTPUT_FORMAT) === JsonOutputFormatter::NAME) {
            return false;
        }
        return $input->getOption(Option::OPTION_OUTPUT_FORMAT) !== CheckstyleOutputFormatter::NAME;
    }

    private function setOnlyRector(string $rector): void
    {
        $this->onlyRector = $this->onlyRuleResolver->resolve($rector);
    }
}
