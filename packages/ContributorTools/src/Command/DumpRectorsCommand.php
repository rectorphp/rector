<?php declare(strict_types=1);

namespace Rector\ContributorTools\Command;

use Rector\Console\Command\AbstractCommand;
use Rector\Console\Shell;
use Rector\ContributorTools\Finder\RectorsFinder;
use Rector\ContributorTools\OutputFormatter\MarkdownOutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class DumpRectorsCommand extends AbstractCommand
{
    /**
     * @var RectorsFinder
     */
    private $rectorsFinder;

    /**
     * @var MarkdownOutputFormatter
     */
    private $markdownOutputFormatter;

    public function __construct(RectorsFinder $rectorsFinder, MarkdownOutputFormatter $markdownOutputFormatter)
    {
        parent::__construct();

        $this->rectorsFinder = $rectorsFinder;
        $this->markdownOutputFormatter = $markdownOutputFormatter;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Generates markdown documentation of all Rectors');
        $this->addOption(
            'output-format',
            'o',
            InputOption::VALUE_REQUIRED,
            'Output format for Rectors [json, markdown]',
            'markdown'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $packageRectors = $this->rectorsFinder->findInDirectory(__DIR__ . '/../../../../packages');
        $generalRectors = $this->rectorsFinder->findInDirectory(__DIR__ . '/../../../../src');

        // default
        $this->markdownOutputFormatter->format($packageRectors, $generalRectors);

        return Shell::CODE_SUCCESS;
    }
}
