<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator\Command;

use Rector\Core\Console\Command\AbstractCommand;
use Rector\Utils\NodeDocumentationGenerator\NodeInfosFactory;
use Rector\Utils\NodeDocumentationGenerator\OutputFormatter\MarkdownDumpNodesOutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class DumpNodesCommand extends AbstractCommand
{
    /**
     * @var MarkdownDumpNodesOutputFormatter
     */
    private $markdownDumpNodesOutputFormatter;

    /**
     * @var NodeInfosFactory
     */
    private $nodeInfosFactory;

    public function __construct(
        MarkdownDumpNodesOutputFormatter $markdownDumpNodesOutputFormatter,
        NodeInfosFactory $nodeInfosFactory
    ) {
        $this->markdownDumpNodesOutputFormatter = $markdownDumpNodesOutputFormatter;
        $this->nodeInfosFactory = $nodeInfosFactory;

        parent::__construct();

        $this->symfonyStyle = $symfonyStyle;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('[DOCS] Dump overview of all Nodes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nodesInfos = $this->nodeInfosFactory->create();
        $this->markdownDumpNodesOutputFormatter->format($nodesInfos);

        return ShellCode::SUCCESS;
    }
}
