<?php declare(strict_types=1);

namespace Rector\Utils\DocumentationGenerator\OutputFormatter\DumpNodes;

use Nette\Utils\Json;
use Rector\Utils\DocumentationGenerator\Contract\OutputFormatter\DumpNodesOutputFormatterInterface;
use Rector\Utils\DocumentationGenerator\Node\NodeInfo;
use Rector\Utils\DocumentationGenerator\Node\NodeInfoResult;

final class JsonDumpNodesOutputFormatter implements DumpNodesOutputFormatterInterface
{
    public function getName(): string
    {
        return 'json';
    }

    public function format(NodeInfoResult $nodeInfoResult): void
    {
        $nodeCategoryData = [];

        foreach ($nodeInfoResult->getNodeInfos() as $category => $nodeInfos) {
            $nodeData = [];

            foreach ($nodeInfos as $nodeInfo) {
                $nodeData[] = [
                    /** @var NodeInfo $nodeInfo */
                    'class' => $nodeInfo->getClass(),
                    'printed_content' => $nodeInfo->getPrintedContent(),
                    'has_required_arguments' => $nodeInfo->hasRequiredArguments(),
                ];
            }

            $nodeCategoryData[] = [
                'category' => $category,
                'nodes' => $nodeData,
            ];
        }

        $data = [
            'node_categories' => $nodeCategoryData,
        ];

        echo Json::encode($data, Json::PRETTY);
    }
}
