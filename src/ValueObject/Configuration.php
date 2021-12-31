<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject;

use RectorPrefix20211231\JetBrains\PhpStorm\Immutable;
use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\ValueObject\Bootstrap\BootstrapConfigs;
use Symplify\SmartFileSystem\SmartFileInfo;
#[Immutable]
final class Configuration
{
    /**
     * @readonly
     * @var bool
     */
    private $isDryRun = \false;
    /**
     * @readonly
     * @var bool
     */
    private $showProgressBar = \true;
    /**
     * @readonly
     * @var bool
     */
    private $shouldClearCache = \false;
    /**
     * @readonly
     * @var string
     */
    private $outputFormat = \Rector\ChangesReporting\Output\ConsoleOutputFormatter::NAME;
    /**
     * @var string[]
     * @readonly
     */
    private $fileExtensions = ['php'];
    /**
     * @var string[]
     * @readonly
     */
    private $paths = [];
    /**
     * @readonly
     * @var bool
     */
    private $showDiffs = \true;
    /**
     * @readonly
     * @var \Rector\Core\ValueObject\Bootstrap\BootstrapConfigs|null
     */
    private $bootstrapConfigs;
    /**
     * @readonly
     * @var string|null
     */
    private $parallelPort = null;
    /**
     * @readonly
     * @var string|null
     */
    private $parallelIdentifier = null;
    /**
     * @readonly
     * @var bool
     */
    private $isParallel = \false;
    /**
     * @param string[] $fileExtensions
     * @param string[] $paths
     * @param string|null $parallelPort
     * @param string|null $parallelIdentifier
     */
    public function __construct(bool $isDryRun = \false, bool $showProgressBar = \true, bool $shouldClearCache = \false, string $outputFormat = \Rector\ChangesReporting\Output\ConsoleOutputFormatter::NAME, array $fileExtensions = ['php'], array $paths = [], bool $showDiffs = \true, ?\Rector\Core\ValueObject\Bootstrap\BootstrapConfigs $bootstrapConfigs = null, $parallelPort = null, $parallelIdentifier = null, bool $isParallel = \false)
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
        $this->isParallel = $isParallel;
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
    public function isParallel() : bool
    {
        return $this->isParallel;
    }
    /**
     * @return string|null
     */
    public function getConfig()
    {
        return $this->getMainConfigFilePath();
    }
}
