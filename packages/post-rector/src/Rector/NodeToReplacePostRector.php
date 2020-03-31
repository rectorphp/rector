<?php

declare(strict_types=1);

namespace Rector\PostRector\Rector;

use PhpParser\Node;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\PostRector\Collector\NodesToReplaceCollector;

final class NodeToReplacePostRector extends AbstractPostRector
{
    /**
     * @var NodesToReplaceCollector
     */
    private $nodesToReplaceCollector;

    public function __construct(NodesToReplaceCollector $nodesToReplaceCollector)
    {
        $this->nodesToReplaceCollector = $nodesToReplaceCollector;
    }

    public function getPriority(): int
    {
        return 1100;
    }

    public function leaveNode(Node $node): ?Node
    {
        foreach ($this->nodesToReplaceCollector->getNodes() as [$nodeToFind, $replacement]) {
            if ($node === $nodeToFind) {
                return $replacement;
            }
        }

        return null;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Post Rector that replaces one nodes  with another');
    }
}
