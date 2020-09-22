<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator\ValueObject;

final class NodeInfos
{
    /**
     * @var NodeInfo[]
     */
    private $nodeInfos;

    /**
     * @param NodeInfo[] $nodeInfos
     */
    public function __construct(array $nodeInfos)
    {
        $this->nodeInfos = $this->sortNodeInfosByClass($nodeInfos);
    }

    /**
     * @return NodeInfo[]
     */
    public function getNodeInfos(): array
    {
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
