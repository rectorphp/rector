<?php

declare(strict_types=1);

namespace Rector\Utils\DocumentationGenerator\Command;

use Rector\Core\Console\Command\AbstractCommand;
use Rector\Core\Console\Shell;
use Rector\Core\Testing\Finder\RectorsFinder;
use Rector\Utils\DocumentationGenerator\Contract\OutputFormatter\DumpRectorsOutputFormatterInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class DumpRectorsCommand extends AbstractCommand
{
    /**
     * @var DumpRectorsOutputFormatterInterface[]
     */
    private $dumpRectorsOutputFormatterInterfaces = [];

    /**
     * @var RectorsFinder
     */
    private $rectorsFinder;

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
        $this->setDescription('[Docs] Dump overview of all Rectors');
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
        $rulesRectors = $this->rectorsFinder->findInDirectories([
            __DIR__ . '/../../../../rules',
            __DIR__ . '/../../../../packages',
        ]);

        $generalRectors = $this->rectorsFinder->findInDirectory(__DIR__ . '/../../../../src');

        foreach ($this->dumpRectorsOutputFormatterInterfaces as $outputFormatter) {
            if ($outputFormatter->getName() !== $input->getOption('output-format')) {
                continue;
            }

            $outputFormatter->format($generalRectors, $rulesRectors);
        }

        return Shell::CODE_SUCCESS;
    }
}
