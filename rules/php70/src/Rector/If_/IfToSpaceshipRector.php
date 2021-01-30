<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
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
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://wiki.php.net/rfc/combined-comparison-operator
 * @see https://3v4l.org/LPbA0
 *
 * @see \Rector\Php70\Tests\Rector\If_\IfToSpaceshipRector\IfToSpaceshipRectorTest
 */
final class IfToSpaceshipRector extends AbstractRector
{
    /**
     * @var int|null
     */
    private $onEqual;

    /**
     * @var int|null
     */
    private $onSmaller;

    /**
     * @var int|null
     */
    private $onGreater;

    /**
     * @var Expr|null
     */
    private $firstValue;

    /**
     * @var Expr|null
     */
    private $secondValue;

    /**
     * @var Node|null
     */
    private $nextNode;

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes if/else to spaceship <=> where useful',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
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
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::SPACESHIP)) {
            return null;
        }

        if (! $node->cond instanceof Equal && ! $node->cond instanceof Identical) {
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

        if (! $this->areVariablesEqual($node->cond, $this->firstValue, $this->secondValue)) {
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
        $spaceship = new Spaceship($this->secondValue, $this->firstValue);

        return new Return_($spaceship);
    }

    private function reset(): void
    {
        $this->onEqual = null;
        $this->onSmaller = null;
        $this->onGreater = null;

        $this->firstValue = null;
        $this->secondValue = null;
    }

    private function matchOnEqualFirstValueAndSecondValue(If_ $if): void
    {
        $this->matchOnEqual($if);

        if ($if->else !== null) {
            $this->processElse($if->else);
        } else {
            $this->nextNode = $if->getAttribute(AttributeKey::NEXT_NODE);
            if ($this->nextNode instanceof Return_ && $this->nextNode->expr instanceof Ternary) {
                /** @var Ternary $ternary */
                $ternary = $this->nextNode->expr;
                $this->processTernary($ternary);
            }
        }
    }

    private function areVariablesEqual(BinaryOp $binaryOp, Expr $firstValue, Expr $secondValue): bool
    {
        if ($this->areNodesEqual($binaryOp->left, $firstValue) && $this->areNodesEqual(
            $binaryOp->right,
            $secondValue
        )) {
            return true;
        }
        if (! $this->areNodesEqual($binaryOp->right, $firstValue)) {
            return false;
        }
        return $this->areNodesEqual($binaryOp->left, $secondValue);
    }

    private function matchOnEqual(If_ $if): void
    {
        if (count($if->stmts) !== 1) {
            return;
        }

        $onlyIfStmt = $if->stmts[0];

        if ($onlyIfStmt instanceof Return_) {
            if ($onlyIfStmt->expr === null) {
                return;
            }

            $this->onEqual = $this->valueResolver->getValue($onlyIfStmt->expr);
        }
    }

    private function processElse(Else_ $else): void
    {
        if (count($else->stmts) !== 1) {
            return;
        }

        if (! $else->stmts[0] instanceof Return_) {
            return;
        }

        /** @var Return_ $returnNode */
        $returnNode = $else->stmts[0];
        if ($returnNode->expr instanceof Ternary) {
            $this->processTernary($returnNode->expr);
        }
    }

    private function processTernary(Ternary $ternary): void
    {
        if ($ternary->cond instanceof Smaller) {
            $this->firstValue = $ternary->cond->left;
            $this->secondValue = $ternary->cond->right;

            if ($ternary->if !== null) {
                $this->onSmaller = $this->valueResolver->getValue($ternary->if);
            }

            $this->onGreater = $this->valueResolver->getValue($ternary->else);
        } elseif ($ternary->cond instanceof Greater) {
            $this->firstValue = $ternary->cond->right;
            $this->secondValue = $ternary->cond->left;

            if ($ternary->if !== null) {
                $this->onGreater = $this->valueResolver->getValue($ternary->if);
            }

            $this->onSmaller = $this->valueResolver->getValue($ternary->else);
        }
    }
}
