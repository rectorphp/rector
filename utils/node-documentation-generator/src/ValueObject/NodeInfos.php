<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator\ValueObject;

final class NodeInfos
{
    /**
     * @var NodeInfo[][]
     */
    private $nodeInfosByCategory = [];

    /**
     * @param NodeInfo[] $nodeInfos
     */
    public function __construct(array $nodeInfos)
    {
        foreach ($nodeInfos as $nodeInfo) {
            $this->nodeInfosByCategory[$nodeInfo->getCategory()][] = $nodeInfo;
        }
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
        ksort($this->nodeInfosByCategory);

        foreach ($this->nodeInfosByCategory as $category => $nodeInfos) {
            $this->nodeInfosByCategory[$category] = $this->sortNodeInfosByClass($nodeInfos);
        }

        return $this->nodeInfosByCategory;
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
