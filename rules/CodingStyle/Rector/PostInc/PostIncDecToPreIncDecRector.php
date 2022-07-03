<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\PostInc;

use PhpParser\Node;
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
     * @return \PhpParser\Node\Expr\PreDec|\PhpParser\Node\Expr\PreInc
     */
    private function processPreFor($node, For_ $for)
    {
        $for->loop = [$this->processPrePost($node)];
        return $for->loop[0];
    }
}
