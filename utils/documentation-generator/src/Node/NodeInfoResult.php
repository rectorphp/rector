<?php

declare(strict_types=1);

namespace Rector\Utils\DocumentationGenerator\Node;

use Rector\Utils\DocumentationGenerator\ValueObject\NodeInfo;

final class NodeInfoResult
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
        return array_keys($this->getNodeInfos());
    }

    /**
     * @return \Rector\Utils\DocumentationGenerator\ValueObject\NodeInfo[][]
     */
    public function getNodeInfos(): array
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
