<?php declare(strict_types=1);

namespace Rector\Utils\DocumentationGenerator\OutputFormatter\DumpNodes;

use Rector\Utils\DocumentationGenerator\Contract\OutputFormatter\DumpNodesOutputFormatterInterface;
use Rector\Utils\DocumentationGenerator\Node\NodeInfo;
use Rector\Utils\DocumentationGenerator\Node\NodeInfoResult;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ConsoleDumpNodesOutputFormatter implements DumpNodesOutputFormatterInterface
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    public function getName(): string
    {
        return 'console';
    }

    public function format(NodeInfoResult $nodeInfoResult): void
    {
        foreach ($nodeInfoResult->getNodeInfos() as $category => $nodeInfos) {
            if (class_exists($category)) {
                $this->symfonyStyle->title(sprintf(' Children of "%s"', $category));
            } else {
                $this->symfonyStyle->title(' ' . $category);
            }

            $tableData = [];
            foreach ($nodeInfos as $nodeInfo) {
                $tableData[] = [
                    /** @var NodeInfo $nodeInfo */
                    $nodeInfo->getClass(),
                    $nodeInfo->getPrintedContent(),
                    $nodeInfo->hasRequiredArguments() ? 'yes' : 'no',
                ];
            }

            $this->symfonyStyle->table(['Node class', 'Content', 'Needs Args'], $tableData);
            $this->symfonyStyle->newLine();
        }
    }
}
