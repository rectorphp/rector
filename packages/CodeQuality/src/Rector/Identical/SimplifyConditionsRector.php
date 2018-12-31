<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use Rector\PhpParser\Node\AssignAndBinaryMap;
use Rector\PhpParser\Node\Maintainer\BinaryOpMaintainer;
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
     * @var BinaryOpMaintainer
     */
    private $binaryOpMaintainer;

    public function __construct(AssignAndBinaryMap $assignAndBinaryMap, BinaryOpMaintainer $binaryOpMaintainer)
    {
        $this->assignAndBinaryMap = $assignAndBinaryMap;
        $this->binaryOpMaintainer = $binaryOpMaintainer;
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

    private function processBooleanNot(BooleanNot $node): ?Node
    {
        if (! $node->expr instanceof BinaryOp) {
            return null;
        }

        if ($this->shouldSkip($node->expr)) {
            return null;
        }

        return $this->createInversedBooleanOp($node->expr);
    }

    private function processIdenticalAndNotIdentical(BinaryOp $node): ?Node
    {
        $matchedNodes = $this->binaryOpMaintainer->matchFirstAndSecondConditionNode(
            $node,
            function (Node $node) {
                return $node instanceof Identical || $node instanceof NotIdentical;
            },
            function (Node $node) {
                return $this->isBool($node);
            }
        );

        if ($matchedNodes === null) {
            return $matchedNodes;
        }

        /** @var Identical|NotIdentical $subBinaryOpNode */
        [$subBinaryOpNode, $otherNode] = $matchedNodes;
        if ($this->isFalse($otherNode)) {
            return $this->createInversedBooleanOp($subBinaryOpNode);
        }

        return $subBinaryOpNode;
    }

    /**
     * Skip too nested binary || binary > binary combinations
     */
    private function shouldSkip(BinaryOp $binaryOpNode): bool
    {
        if ($binaryOpNode instanceof BooleanOr) {
            return true;
        }

        if ($binaryOpNode->left instanceof BinaryOp) {
            return true;
        }
        return $binaryOpNode->right instanceof BinaryOp;
    }

    private function createInversedBooleanOp(BinaryOp $binaryOpNode): ?BinaryOp
    {
        $inversedBinaryClass = $this->assignAndBinaryMap->getInversed($binaryOpNode);
        if ($inversedBinaryClass === null) {
            return null;
        }

        return new $inversedBinaryClass($binaryOpNode->left, $binaryOpNode->right);
    }
}
