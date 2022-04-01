<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\If_\ChangeOrIfReturnToEarlyReturnRector\ChangeOrIfReturnToEarlyReturnRectorTest
 */
final class ChangeOrIfReturnToEarlyReturnRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    public function __construct(\Rector\Core\NodeManipulator\IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes if || with return to early return', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($a, $b)
    {
        if ($a || $b) {
            return null;
        }

        return 'another';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($a, $b)
    {
        if ($a) {
            return null;
        }
        if ($b) {
            return null;
        }

        return 'another';
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
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     * @return null|If_[]
     */
    public function refactor(\PhpParser\Node $node) : ?array
    {
        if (!$this->ifManipulator->isIfWithOnly($node, \PhpParser\Node\Stmt\Return_::class)) {
            return null;
        }
        if (!$node->cond instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
            return null;
        }
        if ($this->isInstanceofCondOnly($node->cond)) {
            return null;
        }
        /** @var Return_ $return */
        $return = $node->stmts[0];
        // same return? skip
        $next = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if ($next instanceof \PhpParser\Node\Stmt\Return_ && $this->nodeComparator->areNodesEqual($return, $next)) {
            return null;
        }
        $ifs = $this->createMultipleIfs($node->cond, $return, []);
        // ensure ifs not removed by other rules
        if ($ifs === []) {
            return null;
        }
        $this->mirrorComments($ifs[0], $node);
        return $ifs;
    }
    /**
     * @param If_[] $ifs
     * @return If_[]
     */
    private function createMultipleIfs(\PhpParser\Node\Expr\BinaryOp\BooleanOr $booleanOr, \PhpParser\Node\Stmt\Return_ $return, array $ifs) : array
    {
        while ($booleanOr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
            $ifs = \array_merge($ifs, $this->collectLeftBooleanOrToIfs($booleanOr, $return, $ifs));
            $ifs[] = $this->createIf($booleanOr->right, $return);
            $booleanOr = $booleanOr->right;
        }
        return $ifs + [$this->createIf($booleanOr, $return)];
    }
    /**
     * @param If_[] $ifs
     * @return If_[]
     */
    private function collectLeftBooleanOrToIfs(\PhpParser\Node\Expr\BinaryOp\BooleanOr $booleanOr, \PhpParser\Node\Stmt\Return_ $return, array $ifs) : array
    {
        $left = $booleanOr->left;
        if (!$left instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr) {
            return [$this->createIf($left, $return)];
        }
        return $this->createMultipleIfs($left, $return, $ifs);
    }
    private function createIf(\PhpParser\Node\Expr $expr, \PhpParser\Node\Stmt\Return_ $return) : \PhpParser\Node\Stmt\If_
    {
        return new \PhpParser\Node\Stmt\If_($expr, ['stmts' => [$return]]);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp|\PhpParser\Node\Expr\BinaryOp\BooleanOr $booleanOr
     */
    private function isInstanceofCondOnly($booleanOr) : bool
    {
        if ($booleanOr->left instanceof \PhpParser\Node\Expr\BinaryOp) {
            return $this->isInstanceofCondOnly($booleanOr->left);
        }
        if ($booleanOr->right instanceof \PhpParser\Node\Expr\BinaryOp) {
            return $this->isInstanceofCondOnly($booleanOr->right);
        }
        return $booleanOr->left instanceof \PhpParser\Node\Expr\Instanceof_ || $booleanOr->right instanceof \PhpParser\Node\Expr\Instanceof_;
    }
}
