<?php

declare(strict_types=1);

namespace Rector\PostRector\Rector;

use PhpParser\Node;
use Rector\PostRector\Collector\NodesToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * This class collects all to-be-added expresssions (= 1 line in code)
 * and then adds new expressions to list of $nodes
 *
 * From:
 * - $this->someCall();
 *
 * To:
 * - $this->someCall();
 * - $value = this->someNewCall(); // added expression
 */
final class NodeAddingPostRector extends AbstractPostRector
{
    /**
     * @var NodesToAddCollector
     */
    private $nodesToAddCollector;

    public function __construct(NodesToAddCollector $nodesToAddCollector)
    {
        $this->nodesToAddCollector = $nodesToAddCollector;
    }

    public function getPriority(): int
    {
        return 1000;
    }

    /**
     * @return array<int|string, Node>|Node
     */
    public function leaveNode(Node $node)
    {
        $newNodes = [$node];

        $nodesToAddAfter = $this->nodesToAddCollector->getNodesToAddAfterNode($node);
        if ($nodesToAddAfter !== []) {
            $this->nodesToAddCollector->clearNodesToAddAfter($node);
            $newNodes = array_merge($newNodes, $nodesToAddAfter);
        }

        $nodesToAddBefore = $this->nodesToAddCollector->getNodesToAddBeforeNode($node);
        if ($nodesToAddBefore !== []) {
            $this->nodesToAddCollector->clearNodesToAddBefore($node);
            $newNodes = array_merge($nodesToAddBefore, $newNodes);
        }

        if ($newNodes === [$node]) {
            return $node;
        }

        return $newNodes;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Post Rector that adds nodes',
        [
            new CodeSample(
            <<<'CODE_SAMPLE'
$value = 1000;
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
$string = new String_(...);
$value = 1000;
CODE_SAMPLE
            ),
        ]);
    }
}
