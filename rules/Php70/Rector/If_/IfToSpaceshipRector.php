<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\Spaceship;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/combined-comparison-operator https://3v4l.org/LPbA0
 *
 * @see \Rector\Tests\Php70\Rector\If_\IfToSpaceshipRector\IfToSpaceshipRectorTest
 */
final class IfToSpaceshipRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var int|float|string|bool|mixed[]|null
     */
    private $onEqual = null;
    /**
     * @var int|float|string|bool|mixed[]|null
     */
    private $onSmaller = null;
    /**
     * @var int|float|string|bool|mixed[]|null
     */
    private $onGreater = null;
    /**
     * @var \PhpParser\Node\Expr|null
     */
    private $firstValue;
    /**
     * @var \PhpParser\Node\Expr|null
     */
    private $secondValue;
    /**
     * @var \PhpParser\Node|null
     */
    private $nextNode = null;
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes if/else to spaceship <=> where useful', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        usort($languages, function ($a, $b) {
            if ($a[0] === $b[0]) {
                return 0;
            }

            return ($a[0] < $b[0]) ? 1 : -1;
        });
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        usort($languages, function ($a, $b) {
            return $b[0] <=> $a[0];
        });
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
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->cond instanceof \PhpParser\Node\Expr\BinaryOp\Equal && !$node->cond instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            return null;
        }
        $this->reset();
        $this->matchOnEqualFirstValueAndSecondValue($node);
        if ($this->firstValue === null) {
            return null;
        }
        if ($this->secondValue === null) {
            return null;
        }
        /** @var Equal|Identical $condition */
        $condition = $node->cond;
        if (!$this->areVariablesEqual($condition, $this->firstValue, $this->secondValue)) {
            return null;
        }
        // is spaceship return values?
        if ([$this->onGreater, $this->onEqual, $this->onSmaller] !== [-1, 0, 1]) {
            return null;
        }
        if ($this->nextNode !== null) {
            $this->removeNode($this->nextNode);
        }
        // spaceship ready!
        $spaceship = new \PhpParser\Node\Expr\BinaryOp\Spaceship($this->secondValue, $this->firstValue);
        return new \PhpParser\Node\Stmt\Return_($spaceship);
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::SPACESHIP;
    }
    private function reset() : void
    {
        $this->onEqual = null;
        $this->onSmaller = null;
        $this->onGreater = null;
        $this->firstValue = null;
        $this->secondValue = null;
    }
    private function matchOnEqualFirstValueAndSecondValue(\PhpParser\Node\Stmt\If_ $if) : void
    {
        $this->matchOnEqual($if);
        if ($if->else !== null) {
            $this->processElse($if->else);
        } else {
            $this->nextNode = $if->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
            if ($this->nextNode instanceof \PhpParser\Node\Stmt\Return_ && $this->nextNode->expr instanceof \PhpParser\Node\Expr\Ternary) {
                /** @var Ternary $ternary */
                $ternary = $this->nextNode->expr;
                $this->processTernary($ternary);
            }
        }
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Equal|\PhpParser\Node\Expr\BinaryOp\Identical $binaryOp
     */
    private function areVariablesEqual($binaryOp, \PhpParser\Node\Expr $firstValue, \PhpParser\Node\Expr $secondValue) : bool
    {
        if ($this->nodeComparator->areNodesEqual($binaryOp->left, $firstValue) && $this->nodeComparator->areNodesEqual($binaryOp->right, $secondValue)) {
            return \true;
        }
        if (!$this->nodeComparator->areNodesEqual($binaryOp->right, $firstValue)) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($binaryOp->left, $secondValue);
    }
    private function matchOnEqual(\PhpParser\Node\Stmt\If_ $if) : void
    {
        if (\count($if->stmts) !== 1) {
            return;
        }
        $onlyIfStmt = $if->stmts[0];
        if ($onlyIfStmt instanceof \PhpParser\Node\Stmt\Return_) {
            if ($onlyIfStmt->expr === null) {
                return;
            }
            $this->onEqual = $this->valueResolver->getValue($onlyIfStmt->expr);
        }
    }
    private function processElse(\PhpParser\Node\Stmt\Else_ $else) : void
    {
        if (\count($else->stmts) !== 1) {
            return;
        }
        if (!$else->stmts[0] instanceof \PhpParser\Node\Stmt\Return_) {
            return;
        }
        /** @var Return_ $returnNode */
        $returnNode = $else->stmts[0];
        if ($returnNode->expr instanceof \PhpParser\Node\Expr\Ternary) {
            $this->processTernary($returnNode->expr);
        }
    }
    private function processTernary(\PhpParser\Node\Expr\Ternary $ternary) : void
    {
        if ($ternary->cond instanceof \PhpParser\Node\Expr\BinaryOp\Smaller) {
            $this->firstValue = $ternary->cond->left;
            $this->secondValue = $ternary->cond->right;
            if ($ternary->if !== null) {
                $this->onSmaller = $this->valueResolver->getValue($ternary->if);
            }
            $this->onGreater = $this->valueResolver->getValue($ternary->else);
        } elseif ($ternary->cond instanceof \PhpParser\Node\Expr\BinaryOp\Greater) {
            $this->firstValue = $ternary->cond->right;
            $this->secondValue = $ternary->cond->left;
            if ($ternary->if !== null) {
                $this->onGreater = $this->valueResolver->getValue($ternary->if);
            }
            $this->onSmaller = $this->valueResolver->getValue($ternary->else);
        }
    }
}
