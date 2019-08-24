<?php declare(strict_types=1);

namespace Rector\Utils\ContributorTools\OutputFormatter\DumpNodes;

use Nette\Utils\Json;
use Rector\Utils\ContributorTools\Contract\OutputFormatter\DumpNodesOutputFormatterInterface;
use Rector\Utils\ContributorTools\Node\NodeInfo;
use Rector\Utils\ContributorTools\Node\NodeInfoResult;

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
