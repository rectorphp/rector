<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\PostInc;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector\PostIncDecToPreIncDecRectorTest
 */
final class PostIncDecToPreIncDecRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use ++$value or --$value  instead of `$value++` or `$value--`', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value = 1)
    {
        $value++; echo $value;
        $value--; echo $value;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value = 1)
    {
        ++$value; echo $value;
        --$value; echo $value;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\PostInc::class, \PhpParser\Node\Expr\PostDec::class];
    }
    /**
     * @param PostInc|PostDec $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($this->isAnExpression($parentNode)) {
            return $this->processPrePost($node);
        }
        if ($parentNode instanceof \PhpParser\Node\Expr\ArrayDimFetch && $this->nodeComparator->areNodesEqual($parentNode->dim, $node)) {
            return $this->processPreArray($node, $parentNode);
        }
        if (!$parentNode instanceof \PhpParser\Node\Stmt\For_) {
            return null;
        }
        if (\count($parentNode->loop) !== 1) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($parentNode->loop[0], $node)) {
            return null;
        }
        return $this->processPreFor($node, $parentNode);
    }
    private function isAnExpression(?\PhpParser\Node $node = null) : bool
    {
        if (!$node instanceof \PhpParser\Node) {
            return \false;
        }
        return $node instanceof \PhpParser\Node\Stmt\Expression;
    }
    /**
     * @param \PhpParser\Node\Expr\PostDec|\PhpParser\Node\Expr\PostInc $node
     * @return \PhpParser\Node\Expr\PreDec|\PhpParser\Node\Expr\PreInc
     */
    private function processPrePost($node)
    {
        if ($node instanceof \PhpParser\Node\Expr\PostInc) {
            return new \PhpParser\Node\Expr\PreInc($node->var);
        }
        return new \PhpParser\Node\Expr\PreDec($node->var);
    }
    /**
     * @param \PhpParser\Node\Expr\PostDec|\PhpParser\Node\Expr\PostInc $node
     */
    private function processPreArray($node, \PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : ?\PhpParser\Node\Expr
    {
        $parentOfArrayDimFetch = $arrayDimFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$this->isAnExpression($parentOfArrayDimFetch)) {
            return null;
        }
        $arrayDimFetch->dim = $node->var;
        $this->nodesToAddCollector->addNodeAfterNode($this->processPrePost($node), $arrayDimFetch);
        return $arrayDimFetch->dim;
    }
    /**
     * @param \PhpParser\Node\Expr\PostDec|\PhpParser\Node\Expr\PostInc $node
     * @return \PhpParser\Node\Expr\PreDec|\PhpParser\Node\Expr\PreInc
     */
    private function processPreFor($node, \PhpParser\Node\Stmt\For_ $for)
    {
        $for->loop = [$this->processPrePost($node)];
        return $for->loop[0];
    }
}
