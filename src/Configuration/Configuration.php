<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

use Jean85\PrettyVersions;
use Nette\Utils\Strings;
use Rector\ChangesReporting\Output\CheckstyleOutputFormatter;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\Core\Exception\Rector\RectorNotFoundOrNotValidRectorClassException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Testing\PHPUnit\PHPUnitEnvironment;
use Symfony\Component\Console\Input\InputInterface;

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
     * @var mixed[]
     */
    private $source = [];

    /**
     * Needs to run in the start of the life cycle, since the rest of workflow uses it.
     */
    public function resolveFromInput(InputInterface $input): void
    {
        $this->isDryRun = (bool) $input->getOption(Option::OPTION_DRY_RUN);
        $this->hideAutoloadErrors = (bool) $input->getOption(Option::HIDE_AUTOLOAD_ERRORS);
        $this->mustMatchGitDiff = (bool) $input->getOption(Option::MATCH_GIT_DIFF);
        $this->showProgressBar = $this->canShowProgressBar($input);

        $outputFileOption = $input->getOption(Option::OPTION_OUTPUT_FILE);
        $this->outputFile = $outputFileOption ? (string) $outputFileOption : null;

        /** @var string|null $onlyRector */
        $onlyRector = $input->getOption(Option::OPTION_ONLY);

        $this->setOnlyRector($onlyRector);
    }

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
     * @param string[] $source
     */
    public function setSource(array $source): void
    {
        $this->source = $source;
    }

    /**
     * @return string[]
     */
    public function getSource(): array
    {
        return $this->source;
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
