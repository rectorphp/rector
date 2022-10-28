<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector\SimplifyIfNotNullReturnRectorTest
 */
final class SimplifyIfNotNullReturnRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    public function __construct(IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes redundant null check to instant return', [new CodeSample(<<<'CODE_SAMPLE'
$newNode = 'something';
if ($newNode !== null) {
    return $newNode;
}

return null;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$newNode = 'something';
return $newNode;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(Node $node) : ?Stmt
    {
        $expr = $this->ifManipulator->matchIfNotNullReturnValue($node);
        if ($expr instanceof Expr) {
            $insideIfNode = $node->stmts[0];
            $nextNode = $node->getAttribute(AttributeKey::NEXT_NODE);
            if (!$nextNode instanceof Return_) {
                return null;
            }
            if ($nextNode->expr === null) {
                return null;
            }
            if (!$this->valueResolver->isNull($nextNode->expr)) {
                return null;
            }
            $this->removeNode($nextNode);
            return $insideIfNode;
        }
        return null;
    }
}
