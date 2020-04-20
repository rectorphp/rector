<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

use Jean85\PrettyVersions;
use Nette\Utils\Strings;
use OndraM\CiDetector\CiDetector;
use Rector\ChangesReporting\Output\CheckstyleOutputFormatter;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\Core\Console\Command\ProcessWorkerCommand;
use Rector\Core\Exception\Rector\RectorNotFoundOrNotValidRectorClassException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Testing\PHPUnit\PHPUnitEnvironment;
use Symfony\Component\Console\Input\InputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
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
    private $hideAutoloadErrors = false;

    /**
     * @var string|null
     */
    private $configFilePath;

    /**
     * @var bool
     */
    private $showProgressBar = true;

    /**
     * @var string|null
     */
    private $onlyRector;

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
     * @var CiDetector
     */
    private $ciDetector;

    /**
     * @var SmartFileInfo[]
     */
    private $fileInfos = [];

    /**
     * @var bool
     */
    private $shouldClearCache = false;

    /**
     * @var bool
     */
    private $isCacheDebug = false;

    /**
     * @var bool
     */
    private $isCacheEnabled = false;

    /**
     * @var bool
     */
    private $isParallelEnabled = false;

    /**
     * @var string[]
     */
    private $fileExtensions = [];

    /**
     * @param string[] $fileExtensions
     */
    public function __construct(CiDetector $ciDetector, bool $isCacheEnabled, array $fileExtensions)
    {
        $this->ciDetector = $ciDetector;
        $this->isCacheEnabled = $isCacheEnabled;
        $this->fileExtensions = $fileExtensions;
    }

    /**
     * Needs to run in the start of the life cycle, since the rest of workflow uses it.
     */
    public function resolveFromInput(InputInterface $input): void
    {
        $this->isDryRun = (bool) $input->getOption(Option::OPTION_DRY_RUN);
        $this->shouldClearCache = (bool) $input->getOption(Option::OPTION_CLEAR_CACHE);
        $this->hideAutoloadErrors = (bool) $input->getOption(Option::HIDE_AUTOLOAD_ERRORS);
        $this->mustMatchGitDiff = (bool) $input->getOption(Option::MATCH_GIT_DIFF);
        $this->showProgressBar = $this->canShowProgressBar($input);
        $this->isCacheDebug = (bool) $input->getOption(Option::CACHE_DEBUG);

        if ($input->hasOption(Option::OPTION_PARALLEL)) {
            $this->isParallelEnabled = (bool) $input->getOption(Option::OPTION_PARALLEL);
        }

        $outputFileOption = $input->hasOption(Option::OPTION_OUTPUT_FILE) ? $input->getOption(Option::OPTION_OUTPUT_FILE) : null;
        $this->outputFile = $outputFileOption ? (string) $outputFileOption : null;

        /** @var string|null $onlyRector */
        $onlyRector = $input->getOption(Option::OPTION_ONLY);

        $this->setOnlyRector($onlyRector);
    }

    /**
     * @api
     */
    public function setFirstResolverConfig(?string $firstResolvedConfig): void
    {
        $this->configFilePath = $firstResolvedConfig;
    }

    public function getConfigFilePath(): ?string
    {
        return $this->configFilePath;
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

    public function shouldHideAutoloadErrors(): bool
    {
        return $this->hideAutoloadErrors;
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
        if (PHPUnitEnvironment::isPHPUnitRun()) {
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

    public function isParallelEnabled(): bool
    {
        return $this->isParallelEnabled;
    }

    /**
     * @return string[]
     */
    public function getFileExtensions(): array
    {
        return $this->fileExtensions;
    }

    private function canShowProgressBar(InputInterface $input): bool
    {
        if ($input->getArgument('command') === CommandNaming::classToName(ProcessWorkerCommand::class)) {
            return false;
        }

        $noProgressBar = (bool) $input->getOption(Option::OPTION_NO_PROGRESS_BAR);
        if ($noProgressBar) {
            return false;
        }

        if ($input->getOption(Option::OPTION_OUTPUT_FORMAT) === JsonOutputFormatter::NAME) {
            return false;
        }
        return $input->getOption(Option::OPTION_OUTPUT_FORMAT) !== CheckstyleOutputFormatter::NAME;
    }

    private function setOnlyRector(?string $rector): void
    {
        if ($rector) {
            $this->ensureIsValidRectorClass($rector);
            $this->onlyRector = $rector;
        } else {
            $this->onlyRector = null;
        }
    }

    private function ensureIsValidRectorClass(string $rector): void
    {
        // simple check
        if (! Strings::endsWith($rector, 'Rector')) {
            throw new RectorNotFoundOrNotValidRectorClassException($rector);
        }

        if (! class_exists($rector)) {
            throw new RectorNotFoundOrNotValidRectorClassException($rector);
        }

        // must inherit from AbstractRector
        if (! in_array(AbstractRector::class, class_parents($rector), true)) {
            throw new RectorNotFoundOrNotValidRectorClassException($rector);
        }
    }
}
