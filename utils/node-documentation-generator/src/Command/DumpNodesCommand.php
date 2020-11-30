<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator\Command;

use Rector\Core\Console\Command\AbstractCommand;
use Rector\Utils\NodeDocumentationGenerator\NodeInfosFactory;
use Rector\Utils\NodeDocumentationGenerator\Printer\MarkdownNodeInfosPrinter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class DumpNodesCommand extends AbstractCommand
{
    /**
     * @var string
     */
    private const OUTPUT_FILE = 'output-file';

    /**
     * @var MarkdownNodeInfosPrinter
     */
    private $markdownNodeInfosPrinter;

    /**
     * @var NodeInfosFactory
     */
    private $nodeInfosFactory;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(
        MarkdownNodeInfosPrinter $markdownNodeInfosPrinter,
        NodeInfosFactory $nodeInfosFactory,
        SmartFileSystem $smartFileSystem,
        SymfonyStyle $symfonyStyle
    ) {
        $this->markdownNodeInfosPrinter = $markdownNodeInfosPrinter;
        $this->nodeInfosFactory = $nodeInfosFactory;
        $this->smartFileSystem = $smartFileSystem;
        $this->symfonyStyle = $symfonyStyle;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('[DOCS] Dump overview of all Nodes');

        $this->addOption(
            self::OUTPUT_FILE,
            null,
            InputOption::VALUE_REQUIRED,
            'Where to output the file',
            getcwd() . '/docs/nodes_overview.md'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputFile = (string) $input->getOption(self::OUTPUT_FILE);

        $nodeInfos = $this->nodeInfosFactory->create();

        $printedContent = $this->markdownNodeInfosPrinter->print($nodeInfos);
        $this->smartFileSystem->dumpFile($outputFile, $printedContent);

        $outputFileFileInfo = new SmartFileInfo($outputFile);
        $message = sprintf(
            'Documentation for "%d" PhpParser Nodes was generated to "%s"',
            count($nodeInfos),
            $outputFileFileInfo->getRelativeFilePathFromCwd()
        );
        $this->symfonyStyle->success($message);

        return ShellCode::SUCCESS;
    }
}
