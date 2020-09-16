<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator\Node;

use Rector\Utils\NodeDocumentationGenerator\ValueObject\NodeInfo;

final class NodeInfoCollector
{
    /**
     * @var NodeInfo[][]
     */
    private $nodeInfos = [];

    public function addNodeInfo(string $category, NodeInfo $nodeInfo): void
    {
        $this->nodeInfos[$category][] = $nodeInfo;
    }

    /**
     * @return string[]
     */
    public function getCategories(): array
    {
        return array_keys($this->getNodeInfosByCategory());
    }

    /**
     * @return array<string, NodeInfo[]>
     */
    public function getNodeInfosByCategory(): array
    {
        ksort($this->nodeInfos);

        foreach ($this->nodeInfos as $category => $nodeInfos) {
            $this->nodeInfos[$category] = $this->sortNodeInfosByClass($nodeInfos);
        }

        return $this->nodeInfos;
    }

    /**
     * @param NodeInfo[] $nodeInfos
     * @return NodeInfo[]
     */
    private function sortNodeInfosByClass(array $nodeInfos): array
    {
        usort($nodeInfos, function (NodeInfo $firstNodeInfo, NodeInfo $secondNodeInfo): int {
            return $firstNodeInfo->getClass() <=> $secondNodeInfo->getClass();
        });

        return $nodeInfos;
    }
}
