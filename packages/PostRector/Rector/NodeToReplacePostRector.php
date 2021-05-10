<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use Rector\PostRector\Collector\NodesToReplaceCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
final class NodeToReplacePostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @var \Rector\PostRector\Collector\NodesToReplaceCollector
     */
    private $nodesToReplaceCollector;
    public function __construct(\Rector\PostRector\Collector\NodesToReplaceCollector $nodesToReplaceCollector)
    {
        $this->nodesToReplaceCollector = $nodesToReplaceCollector;
    }
    public function getPriority() : int
    {
        return 1100;
    }
    public function leaveNode(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($this->nodesToReplaceCollector->getNodes() as [$nodeToFind, $replacement]) {
            if ($node === $nodeToFind) {
                return $replacement;
            }
        }
        return null;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replaces nodes on weird positions', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return $value;
    }
}
CODE_SAMPLE
)]);
    }
}
