<?php

declare(strict_types=1);

namespace Rector\Utils\DocumentationGenerator\Command;

use Rector\Core\Console\Command\AbstractCommand;
use Rector\Core\Testing\Finder\RectorsFinder;
use Rector\Utils\DocumentationGenerator\OutputFormatter\DumpRectors\MarkdownDumpRectorsOutputFormatter;
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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rulesRectors = $this->rectorsFinder->findInDirectories([
            __DIR__ . '/../../../../rules',
            __DIR__ . '/../../../../packages',
        ]);

        $generalRectors = $this->rectorsFinder->findInDirectory(__DIR__ . '/../../../../src');

        $this->markdownDumpRectorsOutputFormatter->format($generalRectors, $rulesRectors);

        return ShellCode::SUCCESS;
    }
}
