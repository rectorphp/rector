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
final class ChangeOrIfReturnToEarlyReturnRector extends AbstractRector
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
        return new RuleDefinition('Changes if || with return to early return', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [If_::class];
    }
    /**
     * @param If_ $node
     * @return null|If_[]
     */
    public function refactor(Node $node) : ?array
    {
        if (!$this->ifManipulator->isIfWithOnly($node, Return_::class)) {
            return null;
        }
        if (!$node->cond instanceof BooleanOr) {
            return null;
        }
        if ($this->isInstanceofCondOnly($node->cond)) {
            return null;
        }
        /** @var Return_ $return */
        $return = $node->stmts[0];
        // same return? skip
        $nextNode = $node->getAttribute(AttributeKey::NEXT_NODE);
        if ($nextNode instanceof Return_ && $this->nodeComparator->areNodesEqual($return, $nextNode)) {
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
    private function createMultipleIfs(BooleanOr $booleanOr, Return_ $return, array $ifs) : array
    {
        while ($booleanOr instanceof BooleanOr) {
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
    private function collectLeftBooleanOrToIfs(BooleanOr $booleanOr, Return_ $return, array $ifs) : array
    {
        $left = $booleanOr->left;
        if (!$left instanceof BooleanOr) {
            return [$this->createIf($left, $return)];
        }
        return $this->createMultipleIfs($left, $return, $ifs);
    }
    private function createIf(Expr $expr, Return_ $return) : If_
    {
        return new If_($expr, ['stmts' => [$return]]);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\BooleanOr|\PhpParser\Node\Expr\BinaryOp $booleanOr
     */
    private function isInstanceofCondOnly($booleanOr) : bool
    {
        if ($booleanOr->left instanceof BinaryOp) {
            return $this->isInstanceofCondOnly($booleanOr->left);
        }
        if ($booleanOr->right instanceof BinaryOp) {
            return $this->isInstanceofCondOnly($booleanOr->right);
        }
        return $booleanOr->left instanceof Instanceof_ || $booleanOr->right instanceof Instanceof_;
    }
}
