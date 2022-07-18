<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\SimplifyIfNullableReturnRectorTest
 */
final class SimplifyIfExactValueReturnValueRector extends AbstractRector
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
        return new RuleDefinition('Changes compared to value and return of expr to direct return', [new CodeSample(<<<'CODE_SAMPLE'
$value = 'something';
if ($value === 52) {
    return 52;
}

return $value;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value = 'something';
return $value;
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
    public function refactor(Node $node) : ?Return_
    {
        $nextNode = $node->getAttribute(AttributeKey::NEXT_NODE);
        if (!$nextNode instanceof Return_) {
            return null;
        }
        $expr = $this->ifManipulator->matchIfValueReturnValue($node);
        if (!$expr instanceof Expr) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($expr, $nextNode->expr)) {
            return null;
        }
        $this->removeNode($nextNode);
        return clone $nextNode;
    }
}
