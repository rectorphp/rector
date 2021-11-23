<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject;

use RectorPrefix20211123\JetBrains\PhpStorm\Immutable;
use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
use Symplify\SmartFileSystem\SmartFileInfo;
#[Immutable]
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
    private $outputFormat = \Rector\ChangesReporting\Output\ConsoleOutputFormatter::NAME;
    /**
     * @var string[]
     */
    private $fileExtensions = ['php'];
    /**
     * @var string[]
     */
    private $paths = [];
    /**
     * @var bool
     */
    private $showDiffs = \true;
    /**
     * @var \Rector\Core\ValueObject\Bootstrap\BootstrapConfigs|null
     */
    private $bootstrapConfigs;
    /**
     * @var string|null
     */
    private $parallelPort = null;
    /**
     * @var string|null
     */
    private $parallelIdentifier = null;
    /**
     * @param string[] $fileExtensions
     * @param string[] $paths
     * @param string|null $parallelPort
     * @param string|null $parallelIdentifier
     */
    public function __construct(bool $isDryRun = \false, bool $showProgressBar = \true, bool $shouldClearCache = \false, string $outputFormat = \Rector\ChangesReporting\Output\ConsoleOutputFormatter::NAME, array $fileExtensions = ['php'], array $paths = [], bool $showDiffs = \true, ?\Rector\Core\ValueObject\Bootstrap\BootstrapConfigs $bootstrapConfigs = null, $parallelPort = null, $parallelIdentifier = null)
    {
        $this->isDryRun = $isDryRun;
        $this->showProgressBar = $showProgressBar;
        $this->shouldClearCache = $shouldClearCache;
        $this->outputFormat = $outputFormat;
        $this->fileExtensions = $fileExtensions;
        $this->paths = $paths;
        $this->showDiffs = $showDiffs;
        $this->bootstrapConfigs = $bootstrapConfigs;
        $this->parallelPort = $parallelPort;
        $this->parallelIdentifier = $parallelIdentifier;
    }
    public function isDryRun() : bool
    {
        return $this->isDryRun;
    }
    public function shouldShowProgressBar() : bool
    {
        return $this->showProgressBar;
    }
    public function shouldClearCache() : bool
    {
        return $this->shouldClearCache;
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
    public function shouldShowDiffs() : bool
    {
        return $this->showDiffs;
    }
    public function getMainConfigFilePath() : ?string
    {
        if ($this->bootstrapConfigs === null) {
            return null;
        }
        $mainConfigFile = $this->bootstrapConfigs->getMainConfigFile();
        if (!\is_string($mainConfigFile)) {
            return null;
        }
        $mainConfigFileInfo = new \Symplify\SmartFileSystem\SmartFileInfo($mainConfigFile);
        return $mainConfigFileInfo->getRelativeFilePathFromCwd();
    }
    public function getParallelPort() : ?string
    {
        return $this->parallelPort;
    }
    public function getParallelIdentifier() : ?string
    {
        return $this->parallelIdentifier;
    }
}
