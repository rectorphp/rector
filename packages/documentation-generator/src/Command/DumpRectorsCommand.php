<?php

declare(strict_types=1);

namespace Rector\DocumentationGenerator\Command;

use Rector\Core\Configuration\Option;
use Rector\Core\Console\Command\AbstractCommand;
use Rector\Core\Testing\Finder\RectorsFinder;
use Rector\DocumentationGenerator\OutputFormatter\MarkdownDumpRectorsOutputFormatter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class DumpRectorsCommand extends AbstractCommand
{
    /**
     * @var RectorsFinder
     */
    private $rectorsFinder;

    /**
     * @var MarkdownDumpRectorsOutputFormatter
     */
    private $markdownDumpRectorsOutputFormatter;

    public function __construct(
        RectorsFinder $rectorsFinder,
        MarkdownDumpRectorsOutputFormatter $markdownDumpRectorsOutputFormatter
    ) {
        parent::__construct();

        $this->rectorsFinder = $rectorsFinder;
        $this->markdownDumpRectorsOutputFormatter = $markdownDumpRectorsOutputFormatter;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('[DOCS] Dump overview of all Rectors');

        $this->addArgument(
            Option::SOURCE,
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'Directories with Rector rules'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $source = (array) $input->getArgument(Option::SOURCE);

        $isRectorProject = $source === [];

        if ($source === []) {
            // fallback to core Rectors
            $rulesRectors = $this->rectorsFinder->findInDirectories([
                __DIR__ . '/../../../../rules',
                __DIR__ . '/../../../../packages',
            ]);

            $generalRectors = $this->rectorsFinder->findInDirectory(__DIR__ . '/../../../../src');
        } else {
            // custom directory
            $rulesRectors = $this->rectorsFinder->findInDirectories($source);
            $generalRectors = [];
        }

        $this->markdownDumpRectorsOutputFormatter->format($rulesRectors, $generalRectors, $isRectorProject);

        return ShellCode::SUCCESS;
    }
}
