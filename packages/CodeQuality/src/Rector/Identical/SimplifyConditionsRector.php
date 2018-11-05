<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use Rector\PhpParser\Node\AssignAndBinaryMap;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyConditionsRector extends AbstractRector
{
    /**
     * @var AssignAndBinaryMap
     */
    private $assignAndBinaryMap;

    public function __construct(AssignAndBinaryMap $assignAndBinaryMap)
    {
        $this->assignAndBinaryMap = $assignAndBinaryMap;
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

        if ($node instanceof Identical) {
            return $this->processIdenticalAndNotIdentical($node);
        }

        return null;
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
        if ($node->left instanceof Identical || $node->left instanceof NotIdentical) {
            $subBinaryOpNode = $node->left;
            $shouldInverse = $this->isFalse($node->right);
        } elseif ($node->right instanceof Identical || $node->right instanceof NotIdentical) {
            $subBinaryOpNode = $node->right;
            $shouldInverse = $this->isFalse($node->left);
        } else {
            return null;
        }

        if ($shouldInverse) {
            return $this->createInversedBooleanOp($subBinaryOpNode);
        }

        return $subBinaryOpNode;
    }

    /**
     * Skip too nested binary || binary > binary combinations
     */
    private function shouldSkip(BinaryOp $binaryOpNode): bool
    {
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
