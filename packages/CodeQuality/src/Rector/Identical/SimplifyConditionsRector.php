<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use Rector\PhpParser\Node\AssignAndBinaryMap;
use Rector\PhpParser\Node\Manipulator\BinaryOpManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyConditionsRector extends AbstractRector
{
    /**
     * @var AssignAndBinaryMap
     */
    private $assignAndBinaryMap;

    /**
     * @var BinaryOpManipulator
     */
    private $binaryOpManipulator;

    public function __construct(AssignAndBinaryMap $assignAndBinaryMap, BinaryOpManipulator $binaryOpManipulator)
    {
        $this->assignAndBinaryMap = $assignAndBinaryMap;
        $this->binaryOpManipulator = $binaryOpManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Simplify conditions',
            [new CodeSample("if (! (\$foo !== 'bar')) {...", "if (\$foo === 'bar') {...")]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [BooleanNot::class, Identical::class];
    }

    /**
     * @param BooleanNot|Identical $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof BooleanNot) {
            return $this->processBooleanNot($node);
        }

        return $this->processIdenticalAndNotIdentical($node);
    }

    private function processBooleanNot(BooleanNot $booleanNot): ?Node
    {
        if (! $booleanNot->expr instanceof BinaryOp) {
            return null;
        }

        if ($this->shouldSkip($booleanNot->expr)) {
            return null;
        }

        return $this->createInversedBooleanOp($booleanNot->expr);
    }

    private function processIdenticalAndNotIdentical(BinaryOp $binaryOp): ?Node
    {
        $matchedNodes = $this->binaryOpManipulator->matchFirstAndSecondConditionNode(
            $binaryOp,
            function (Node $binaryOp) {
                return $binaryOp instanceof Identical || $binaryOp instanceof NotIdentical;
            },
            function (Node $binaryOp) {
                return $this->isBool($binaryOp);
            }
        );

        if ($matchedNodes === null) {
            return $matchedNodes;
        }

        /** @var Identical|NotIdentical $subBinaryOp */
        [$subBinaryOp, $otherNode] = $matchedNodes;
        if ($this->isFalse($otherNode)) {
            return $this->createInversedBooleanOp($subBinaryOp);
        }

        return $subBinaryOp;
    }

    /**
     * Skip too nested binary || binary > binary combinations
     */
    private function shouldSkip(BinaryOp $binaryOp): bool
    {
        if ($binaryOp instanceof BooleanOr) {
            return true;
        }

        if ($binaryOp->left instanceof BinaryOp) {
            return true;
        }
        return $binaryOp->right instanceof BinaryOp;
    }

    private function createInversedBooleanOp(BinaryOp $binaryOp): ?BinaryOp
    {
        $inversedBinaryClass = $this->assignAndBinaryMap->getInversed($binaryOp);
        if ($inversedBinaryClass === null) {
            return null;
        }

        return new $inversedBinaryClass($binaryOp->left, $binaryOp->right);
    }
}
