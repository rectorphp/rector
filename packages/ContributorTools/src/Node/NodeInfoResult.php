<?php declare(strict_types=1);

namespace Rector\ContributorTools\Node;

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
     * @return NodeInfo[][]
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
        usort($nodeInfos, function (NodeInfo $firstNodeInfo, NodeInfo $secondNodeInfo) {
            return $firstNodeInfo->getClass() <=> $secondNodeInfo->getClass();
        });

        return $nodeInfos;
    }
}
