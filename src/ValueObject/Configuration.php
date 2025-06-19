<?php

declare (strict_types=1);
namespace Rector\ValueObject;

use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\ValueObject\Configuration\LevelOverflow;
use RectorPrefix202506\Webmozart\Assert\Assert;
final class Configuration
{
    /**
     * @readonly
     */
    private bool $isDryRun = \false;
    /**
     * @readonly
     */
    private bool $showProgressBar = \true;
    /**
     * @readonly
     */
    private bool $shouldClearCache = \false;
    /**
     * @readonly
     */
    private string $outputFormat = ConsoleOutputFormatter::NAME;
    /**
     * @var string[]
     * @readonly
     */
    private array $fileExtensions = ['php'];
    /**
     * @var string[]
     * @readonly
     */
    private array $paths = [];
    /**
     * @readonly
     */
    private bool $showDiffs = \true;
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
     */
    private bool $isParallel = \false;
    /**
     * @readonly
     * @var string|null
     */
    private $memoryLimit = null;
    /**
     * @readonly
     */
    private bool $isDebug = \false;
    /**
     * @readonly
     */
    private bool $reportingWithRealPath = \false;
    /**
     * @readonly
     */
    private ?string $onlyRule = null;
    /**
     * @readonly
     */
    private ?string $onlySuffix = null;
    /**
     * @var LevelOverflow[]
     * @readonly
     */
    private array $levelOverflows = [];
    /**
     * @var positive-int|null
     * @readonly
     */
    private ?int $kaizenStepCount = null;
    /**
     * @param string[] $fileExtensions
     * @param string[] $paths
     * @param LevelOverflow[] $levelOverflows
     * @param positive-int|null $kaizenStepCount
     */
    public function __construct(bool $isDryRun = \false, bool $showProgressBar = \true, bool $shouldClearCache = \false, string $outputFormat = ConsoleOutputFormatter::NAME, array $fileExtensions = ['php'], array $paths = [], bool $showDiffs = \true, ?string $parallelPort = null, ?string $parallelIdentifier = null, bool $isParallel = \false, ?string $memoryLimit = null, bool $isDebug = \false, bool $reportingWithRealPath = \false, ?string $onlyRule = null, ?string $onlySuffix = null, array $levelOverflows = [], ?int $kaizenStepCount = null)
    {
        $this->isDryRun = $isDryRun;
        $this->showProgressBar = $showProgressBar;
        $this->shouldClearCache = $shouldClearCache;
        $this->outputFormat = $outputFormat;
        $this->fileExtensions = $fileExtensions;
        $this->paths = $paths;
        $this->showDiffs = $showDiffs;
        $this->parallelPort = $parallelPort;
        $this->parallelIdentifier = $parallelIdentifier;
        $this->isParallel = $isParallel;
        $this->memoryLimit = $memoryLimit;
        $this->isDebug = $isDebug;
        $this->reportingWithRealPath = $reportingWithRealPath;
        $this->onlyRule = $onlyRule;
        $this->onlySuffix = $onlySuffix;
        $this->levelOverflows = $levelOverflows;
        $this->kaizenStepCount = $kaizenStepCount;
        if (\is_int($kaizenStepCount)) {
            Assert::positiveInteger($kaizenStepCount, 'Change "--kaizen" value to a positive integer');
        }
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
        Assert::notEmpty($this->fileExtensions);
        return $this->fileExtensions;
    }
    public function getOnlyRule() : ?string
    {
        return $this->onlyRule;
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
    public function getMemoryLimit() : ?string
    {
        return $this->memoryLimit;
    }
    public function isDebug() : bool
    {
        return $this->isDebug;
    }
    public function isReportingWithRealPath() : bool
    {
        return $this->reportingWithRealPath;
    }
    public function getOnlySuffix() : ?string
    {
        return $this->onlySuffix;
    }
    /**
     * @return LevelOverflow[]
     */
    public function getLevelOverflows() : array
    {
        return $this->levelOverflows;
    }
    /**
     * @return string[]
     */
    public function getBothSetAndRulesDuplicatedRegistrations() : array
    {
        $rootStandaloneRegisteredRules = SimpleParameterProvider::provideArrayParameter(Option::ROOT_STANDALONE_REGISTERED_RULES);
        $setRegisteredRules = SimpleParameterProvider::provideArrayParameter(Option::SET_REGISTERED_RULES);
        $ruleDuplicatedRegistrations = \array_intersect($rootStandaloneRegisteredRules, $setRegisteredRules);
        return \array_unique($ruleDuplicatedRegistrations);
    }
    /**
     * @return positive-int
     */
    public function getKaizenStepCount() : int
    {
        Assert::notNull($this->kaizenStepCount);
        return $this->kaizenStepCount;
    }
    public function isKaizenEnabled() : bool
    {
        return $this->kaizenStepCount !== null;
    }
}
