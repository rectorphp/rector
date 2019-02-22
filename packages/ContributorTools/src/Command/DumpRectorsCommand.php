<?php declare(strict_types=1);

namespace Rector\ContributorTools\Command;

use Rector\Console\Command\AbstractCommand;
use Rector\Console\Shell;
use Rector\ContributorTools\Contract\OutputFormatter\DumpRectorsOutputFormatterInterface;
use Rector\ContributorTools\Finder\RectorsFinder;
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
     * @var DumpRectorsOutputFormatterInterface[]
     */
    private $dumpRectorsOutputFormatterInterfaces = [];

    /**
     * @param DumpRectorsOutputFormatterInterface[] $dumpRectorsOutputFormatterInterfaces
     */
    public function __construct(RectorsFinder $rectorsFinder, array $dumpRectorsOutputFormatterInterfaces)
    {
        parent::__construct();

        $this->rectorsFinder = $rectorsFinder;
        $this->dumpRectorsOutputFormatterInterfaces = $dumpRectorsOutputFormatterInterfaces;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Dump overview of all Rectors in desired format');
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

        foreach ($this->dumpRectorsOutputFormatterInterfaces as $outputFormatter) {
            if ($outputFormatter->getName() !== $input->getOption('output-format')) {
                continue;
            }

            $outputFormatter->format($generalRectors, $packageRectors);
        }

        return Shell::CODE_SUCCESS;
    }
}
