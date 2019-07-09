<?php declare(strict_types=1);

namespace Rector\Configuration;

use Jean85\PrettyVersions;
use Nette\Utils\Strings;
use Rector\Console\Output\JsonOutputFormatter;
use Rector\Exception\Rector\RectorNotFoundOrNotValidRectorClassException;
use Rector\Rector\AbstractRector;
use Symfony\Component\Console\Input\InputInterface;
use Symplify\PackageBuilder\Configuration\ConfigFileFinder;

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
     * Files and directories to by analysed
     * @var string[]
     */
    private $source = [];

    /**
     * @var string
     */
    private $outputFormat;

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
    private $rule;

    /**
     * Needs to run in the start of the life cycle, since the rest of workflow uses it.
     */
    public function resolveFromInput(InputInterface $input): void
    {
        $this->isDryRun = (bool) $input->getOption(Option::OPTION_DRY_RUN);
        $this->source = (array) $input->getArgument(Option::SOURCE);
        $this->hideAutoloadErrors = (bool) $input->getOption(Option::HIDE_AUTOLOAD_ERRORS);
        $this->outputFormat = (string) $input->getOption(Option::OPTION_OUTPUT_FORMAT);
        $this->showProgressBar = $this->canShowProgressBar($input);

        $this->setRule($input->getOption(Option::OPTION_RULE));
    }

    public function setConfigFilePathFromInput(InputInterface $input): void
    {
        if ($input->getParameterOption('--config')) {
            $this->configFilePath = $input->getParameterOption('--config');
            return;
        }

        if ($input->getParameterOption('-c')) {
            $this->configFilePath = $input->getParameterOption('-c');
            return;
        }

        $this->configFilePath = ConfigFileFinder::provide('rector');
    }

    public function getConfigFilePath(): ?string
    {
        return $this->configFilePath;
    }

    public function getOutputFormat(): string
    {
        return $this->outputFormat;
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

    /**
     * @return string[]
     */
    public function getSource(): array
    {
        return $this->source;
    }

    public function shouldHideAutoloadErrors(): bool
    {
        return $this->hideAutoloadErrors;
    }

    public function showProgressBar(): bool
    {
        return $this->showProgressBar;
    }

    public function getRule(): ?string
    {
        return $this->rule;
    }

    private function canShowProgressBar(InputInterface $input): bool
    {
        return $input->getOption(Option::OPTION_OUTPUT_FORMAT) !== JsonOutputFormatter::NAME;
    }

    private function setRule(?string $rule): void
    {
        if ($rule) {
            $this->ensureIsValidRectorClass($rule);
            $this->rule = $rule;
        } else {
            $this->rule = null;
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
