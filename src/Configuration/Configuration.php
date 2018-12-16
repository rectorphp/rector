<?php declare(strict_types=1);

namespace Rector\Configuration;

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
     * @var bool
     */
    private $withStyle = false;

    /**
     * Files and directories to by analysed
     * @var string[]
     */
    private $source = [];

    /**
     * Needs to run in the start of the life cycle, since the rest of workflow uses it.
     */
    public function resolveFromInput(InputInterface $input): void
    {
        $this->isDryRun = (bool) $input->getOption(Option::OPTION_DRY_RUN);
        $this->source = (array) $input->getArgument(Option::SOURCE);
        $this->hideAutoloadErrors = (bool) $input->getOption(Option::HIDE_AUTOLOAD_ERRORS);
        $this->withStyle = (bool) $input->getOption(Option::OPTION_WITH_STYLE);
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

    public function isWithStyle(): bool
    {
        return $this->withStyle;
    }
}
