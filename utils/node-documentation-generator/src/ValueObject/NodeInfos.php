<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator\ValueObject;

final class NodeInfos
{
    /**
     * @var array<string, NodeInfo[]>
     */
    private $nodeInfosByCategory = [];

    /**
     * @param NodeInfo[] $nodeInfos
     */
    public function __construct(array $nodeInfos)
    {
        $nodeInfosByCategory = [];
        foreach ($nodeInfos as $nodeInfo) {
            $nodeInfosByCategory[$nodeInfo->getCategory()][] = $nodeInfo;
        }

        $this->nodeInfosByCategory = $this->sortNodeInfosByCategory($nodeInfosByCategory);
    }

    /**
     * @return string[]
     */
    public function getCategories(): array
    {
        return array_keys($this->nodeInfosByCategory);
    }

    /**
     * @return array<string, NodeInfo[]>
     */
    public function getNodeInfosByCategory(): array
    {
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

    /**
     * @param array<string, NodeInfo[]> $nodeInfosByCategory
     * @return array<string, NodeInfo[]>
     */
    private function sortNodeInfosByCategory(array $nodeInfosByCategory): array
    {
        ksort($nodeInfosByCategory);

        foreach ($nodeInfosByCategory as $category => $nodeInfos) {
            $nodeInfosByCategory[$category] = $this->sortNodeInfosByClass($nodeInfos);
        }

        return $nodeInfosByCategory;
    }
}
