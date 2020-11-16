<?php

declare(strict_types=1);

namespace Rector\PostRector\Rector;

use PhpParser\Node;
use Rector\PostRector\Collector\NodesToReplaceCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

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

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Post Rector that replaces one nodes with another', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$string = new String_(...);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$value = 1000;
CODE_SAMPLE
            ),
        ]);
    }
}
