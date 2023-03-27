<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\SimplifyIfExactValueReturnValueRector\SimplifyIfExactValueReturnValueRectorTest
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?StmtsAwareInterface
    {
        foreach ((array) $node->stmts as $key => $stmt) {
            if (!$stmt instanceof If_) {
                continue;
            }
            // on last stmt already
            if (!isset($node->stmts[$key + 1])) {
                return null;
            }
            $nextNode = $node->stmts[$key + 1];
            if (!$nextNode instanceof Return_) {
                continue;
            }
            $expr = $this->ifManipulator->matchIfValueReturnValue($stmt);
            if (!$expr instanceof Expr) {
                return null;
            }
            if (!$this->nodeComparator->areNodesEqual($expr, $nextNode->expr)) {
                return null;
            }
            unset($node->stmts[$key]);
            return $node;
        }
        return null;
    }
}
