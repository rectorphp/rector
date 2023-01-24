<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use Rector\Core\NodeDecorator\MixPhpHtmlDecorator;
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
final class NodeAddingPostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    /**
     * @readonly
     * @var \Rector\Core\NodeDecorator\MixPhpHtmlDecorator
     */
    private $mixPhpHtmlDecorator;
    public function __construct(NodesToAddCollector $nodesToAddCollector, MixPhpHtmlDecorator $mixPhpHtmlDecorator)
    {
        $this->nodesToAddCollector = $nodesToAddCollector;
        $this->mixPhpHtmlDecorator = $mixPhpHtmlDecorator;
    }
    public function getPriority() : int
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
            $newNodes = \array_merge($newNodes, $nodesToAddAfter);
            $this->mixPhpHtmlDecorator->decorateAfter($node, \array_merge([$node], \is_array($nodesToAddAfter) ? $nodesToAddAfter : \iterator_to_array($nodesToAddAfter)));
        }
        $nodesToAddBefore = $this->nodesToAddCollector->getNodesToAddBeforeNode($node);
        if ($nodesToAddBefore !== []) {
            $this->nodesToAddCollector->clearNodesToAddBefore($node);
            $newNodes = \array_merge($nodesToAddBefore, $newNodes);
            $this->mixPhpHtmlDecorator->decorateBefore($node);
        }
        if ($newNodes === [$node]) {
            return $node;
        }
        return $newNodes;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add nodes on weird positions', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        return 1;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            return 1;
        }
    }
}
CODE_SAMPLE
)]);
    }
}
