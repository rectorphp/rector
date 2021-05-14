<?php

declare (strict_types=1);
namespace Rector\Core\Configuration;

use RectorPrefix20210514\Jean85\PrettyVersions;
use RectorPrefix20210514\Nette\Utils\Strings;
use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\Exception\Configuration\InvalidConfigurationException;
use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
use RectorPrefix20210514\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210514\Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileInfo;
final class Configuration
{
    /**
     * @var bool
     */
    private $isDryRun = \false;
    /**
     * @var bool
     */
    private $showProgressBar = \true;
    /**
     * @var bool
     */
    private $shouldClearCache = \false;
    /**
     * @var string
     */
    private $outputFormat;
    /**
     * @var bool
     */
    private $isCacheDebug = \false;
    /**
     * @var bool
     */
    private $isCacheEnabled = \false;
    /**
     * @var string[]
     */
    private $fileExtensions = [];
    /**
     * @var string[]
     */
    private $paths = [];
    /**
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @var string|null
     */
    private $outputFile;
    /**
     * @var bool
     */
    private $showDiffs = \true;
    /**
     * @var \Rector\Core\ValueObject\Bootstrap\BootstrapConfigs|null
     */
    private $bootstrapConfigs;
    public function __construct(\RectorPrefix20210514\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider)
    {
        $this->isCacheEnabled = (bool) $parameterProvider->provideParameter(\Rector\Core\Configuration\Option::ENABLE_CACHE);
        $this->fileExtensions = (array) $parameterProvider->provideParameter(\Rector\Core\Configuration\Option::FILE_EXTENSIONS);
        $this->paths = (array) $parameterProvider->provideParameter(\Rector\Core\Configuration\Option::PATHS);
        $this->parameterProvider = $parameterProvider;
    }
    /**
     * Needs to run in the start of the life cycle, since the rest of workflow uses it.
     */
    public function resolveFromInput(\RectorPrefix20210514\Symfony\Component\Console\Input\InputInterface $input) : void
    {
        $this->isDryRun = (bool) $input->getOption(\Rector\Core\Configuration\Option::OPTION_DRY_RUN);
        $this->shouldClearCache = (bool) $input->getOption(\Rector\Core\Configuration\Option::OPTION_CLEAR_CACHE);
        $this->showProgressBar = $this->canShowProgressBar($input);
        $this->showDiffs = !(bool) $input->getOption(\Rector\Core\Configuration\Option::OPTION_NO_DIFFS);
        $this->isCacheDebug = (bool) $input->getOption(\Rector\Core\Configuration\Option::CACHE_DEBUG);
        /** @var string|null $outputFileOption */
        $outputFileOption = $input->getOption(\Rector\Core\Configuration\Option::OPTION_OUTPUT_FILE);
        $this->outputFile = $this->sanitizeOutputFileValue($outputFileOption);
        $this->outputFormat = (string) $input->getOption(\Rector\Core\Configuration\Option::OPTION_OUTPUT_FORMAT);
        $commandLinePaths = (array) $input->getArgument(\Rector\Core\Configuration\Option::SOURCE);
        // manual command line value has priority
        if ($commandLinePaths !== []) {
            $commandLinePaths = $this->correctBashSpacePaths($commandLinePaths);
            $this->paths = $commandLinePaths;
        }
    }
    public function getPrettyVersion() : string
    {
        $version = \RectorPrefix20210514\Jean85\PrettyVersions::getVersion('rector/rector');
        return $version->getPrettyVersion();
    }
    /**
     * @forTests
     */
    public function setIsDryRun(bool $isDryRun) : void
    {
        $this->isDryRun = $isDryRun;
    }
    public function isDryRun() : bool
    {
        return $this->isDryRun;
    }
    public function shouldShowProgressBar() : bool
    {
        if ($this->isCacheDebug) {
            return \false;
        }
        return $this->showProgressBar;
    }
    public function getOutputFile() : ?string
    {
        return $this->outputFile;
    }
    public function shouldClearCache() : bool
    {
        return $this->shouldClearCache;
    }
    public function isCacheDebug() : bool
    {
        return $this->isCacheDebug;
    }
    public function isCacheEnabled() : bool
    {
        return $this->isCacheEnabled;
    }
    /**
     * @return string[]
     */
    public function getFileExtensions() : array
    {
        return $this->fileExtensions;
    }
    /**
     * @return string[]
     */
    public function getPaths() : array
    {
        return $this->paths;
    }
    public function getOutputFormat() : string
    {
        return $this->outputFormat;
    }
    public function validateConfigParameters() : void
    {
        $symfonyContainerXmlPath = (string) $this->parameterProvider->provideParameter(\Rector\Core\Configuration\Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER);
        if ($symfonyContainerXmlPath === '') {
            return;
        }
        if (\file_exists($symfonyContainerXmlPath)) {
            return;
        }
        $message = \sprintf('Path "%s" for "$parameters->set(Option::%s, ...);" in your config was not found. Correct it', $symfonyContainerXmlPath, 'SYMFONY_CONTAINER_XML_PATH_PARAMETER');
        throw new \Rector\Core\Exception\Configuration\InvalidConfigurationException($message);
    }
    public function shouldHideClutter() : bool
    {
        return $this->outputFormat !== \Rector\ChangesReporting\Output\ConsoleOutputFormatter::NAME;
    }
    public function shouldShowDiffs() : bool
    {
        return $this->showDiffs;
    }
    public function setBootstrapConfigs(\Rector\Core\ValueObject\Bootstrap\BootstrapConfigs $bootstrapConfigs) : void
    {
        $this->bootstrapConfigs = $bootstrapConfigs;
    }
    public function getMainConfigFilePath() : ?string
    {
        if ($this->bootstrapConfigs === null) {
            return null;
        }
        $mainConfigFileInfo = $this->bootstrapConfigs->getMainConfigFileInfo();
        if (!$mainConfigFileInfo instanceof \Symplify\SmartFileSystem\SmartFileInfo) {
            return null;
        }
        return $mainConfigFileInfo->getRelativeFilePathFromCwd();
    }
    private function canShowProgressBar(\RectorPrefix20210514\Symfony\Component\Console\Input\InputInterface $input) : bool
    {
        $noProgressBar = (bool) $input->getOption(\Rector\Core\Configuration\Option::OPTION_NO_PROGRESS_BAR);
        if ($noProgressBar) {
            return \false;
        }
        $optionOutputFormat = $input->getOption(\Rector\Core\Configuration\Option::OPTION_OUTPUT_FORMAT);
        return $optionOutputFormat === \Rector\ChangesReporting\Output\ConsoleOutputFormatter::NAME;
    }
    private function sanitizeOutputFileValue(?string $outputFileOption) : ?string
    {
        if ($outputFileOption === '') {
            return null;
        }
        return $outputFileOption;
    }
    /**
     * @param string[] $commandLinePaths
     * @return string[]
     */
    private function correctBashSpacePaths(array $commandLinePaths) : array
    {
        // fixes bash edge-case that to merges string with space to one
        foreach ($commandLinePaths as $commandLinePath) {
            if (\RectorPrefix20210514\Nette\Utils\Strings::contains($commandLinePath, ' ')) {
                $commandLinePaths = \explode(' ', $commandLinePath);
            }
        }
        return $commandLinePaths;
    }
}
