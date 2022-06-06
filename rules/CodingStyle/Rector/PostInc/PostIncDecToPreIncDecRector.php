<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodingStyle\Rector\PostInc;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\PostDec;
use RectorPrefix20220606\PhpParser\Node\Expr\PostInc;
use RectorPrefix20220606\PhpParser\Node\Expr\PreDec;
use RectorPrefix20220606\PhpParser\Node\Expr\PreInc;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\For_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector\PostIncDecToPreIncDecRectorTest
 */
final class PostIncDecToPreIncDecRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use ++$value or --$value  instead of `$value++` or `$value--`', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [PostInc::class, PostDec::class];
    }
    /**
     * @param PostInc|PostDec $node
     */
    public function refactor(Node $node) : ?Node
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($this->isAnExpression($parentNode)) {
            return $this->processPrePost($node);
        }
        if ($parentNode instanceof ArrayDimFetch && $this->nodeComparator->areNodesEqual($parentNode->dim, $node)) {
            return $this->processPreArray($node, $parentNode);
        }
        if (!$parentNode instanceof For_) {
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
    private function isAnExpression(?Node $node = null) : bool
    {
        if (!$node instanceof Node) {
            return \false;
        }
        return $node instanceof Expression;
    }
    /**
     * @param \PhpParser\Node\Expr\PostInc|\PhpParser\Node\Expr\PostDec $node
     * @return \PhpParser\Node\Expr\PreInc|\PhpParser\Node\Expr\PreDec
     */
    private function processPrePost($node)
    {
        if ($node instanceof PostInc) {
            return new PreInc($node->var);
        }
        return new PreDec($node->var);
    }
    /**
     * @param \PhpParser\Node\Expr\PostInc|\PhpParser\Node\Expr\PostDec $node
     */
    private function processPreArray($node, ArrayDimFetch $arrayDimFetch) : ?Expr
    {
        $parentOfArrayDimFetch = $arrayDimFetch->getAttribute(AttributeKey::PARENT_NODE);
        if (!$this->isAnExpression($parentOfArrayDimFetch)) {
            return null;
        }
        $arrayDimFetch->dim = $node->var;
        $this->nodesToAddCollector->addNodeAfterNode($this->processPrePost($node), $arrayDimFetch);
        return $arrayDimFetch->dim;
    }
    /**
     * @param \PhpParser\Node\Expr\PostInc|\PhpParser\Node\Expr\PostDec $node
     * @return \PhpParser\Node\Expr\PreDec|\PhpParser\Node\Expr\PreInc
     */
    private function processPreFor($node, For_ $for)
    {
        $for->loop = [$this->processPrePost($node)];
        return $for->loop[0];
    }
}
