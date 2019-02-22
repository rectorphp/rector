<?php declare(strict_types=1);

namespace Rector\Php\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\Spaceship;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/combined-comparison-operator
 * @see https://3v4l.org/LPbA0
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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes if/else to spaceship <=> where useful', [
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
        $this->reset();

        if ($node->cond instanceof Equal || $node->cond instanceof Identical) {
            if ($node->stmts[0] instanceof Return_) {
                if ($node->stmts[0]->expr === null) {
                    return null;
                }

                $this->onEqual = $this->getValue($node->stmts[0]->expr);
            }
        } else {
            return null;
        }

        if ($node->else !== null) {
            if (count($node->else->stmts) !== 1) {
                return null;
            }

            if ($node->else->stmts[0] instanceof Return_) {
                /** @var Return_ $returnNode */
                $returnNode = $node->else->stmts[0];
                if ($returnNode->expr instanceof Ternary) {
                    $this->processTernary($returnNode->expr);
                }
            }
        } else {
            $nextNode = $node->getAttribute(Attribute::NEXT_NODE);
            if ($nextNode instanceof Return_) {
                if ($nextNode->expr instanceof Ternary) {
                    $this->processTernary($nextNode->expr);
                }
            }
        }

        if ($this->firstValue === null || $this->secondValue === null) {
            return null;
        }

        if (! $this->areVariablesEqual($node->cond, $this->firstValue, $this->secondValue)) {
            return null;
        }

        // is spaceship retun values?
        if ([$this->onGreater, $this->onEqual, $this->onSmaller] !== [-1, 0, 1]) {
            return null;
        }

        if (isset($nextNode)) {
            $this->removeNode($nextNode);
        }

        // spaceship ready!
        $spaceshipNode = new Spaceship($this->secondValue, $this->firstValue);

        return new Return_($spaceshipNode);
    }

    private function reset(): void
    {
        $this->onEqual = null;
        $this->onSmaller = null;
        $this->onGreater = null;

        $this->firstValue = null;
        $this->secondValue = null;
    }

    private function processTernary(Ternary $ternary): void
    {
        if ($ternary->cond instanceof Smaller) {
            $this->firstValue = $ternary->cond->left;
            $this->secondValue = $ternary->cond->right;

            if ($ternary->if !== null) {
                $this->onSmaller = $this->getValue($ternary->if);
            }

            $this->onGreater = $this->getValue($ternary->else);
        } elseif ($ternary->cond instanceof Greater) {
            $this->firstValue = $ternary->cond->right;
            $this->secondValue = $ternary->cond->left;

            if ($ternary->if !== null) {
                $this->onGreater = $this->getValue($ternary->if);
            }

            $this->onSmaller = $this->getValue($ternary->else);
        }
    }

    private function areVariablesEqual(BinaryOp $binaryOp, ?Expr $firstValue, ?Expr $secondValue): bool
    {
        if ($firstValue === null || $secondValue === null) {
            return false;
        }

        if ($this->areNodesEqual($binaryOp->left, $firstValue) && $this->areNodesEqual(
            $binaryOp->right,
            $secondValue
        )) {
            return true;
        }

        if ($this->areNodesEqual($binaryOp->right, $firstValue) && $this->areNodesEqual(
            $binaryOp->left,
            $secondValue
        )) {
            return true;
        }

        return false;
    }
}
