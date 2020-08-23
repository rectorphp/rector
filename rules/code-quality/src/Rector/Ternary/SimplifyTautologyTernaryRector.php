<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\Ternary;
use Rector\Core\PhpParser\Node\Manipulator\BinaryOpManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Ternary\SimplifyTautologyTernaryRector\SimplifyTautologyTernaryRectorTest
 */
final class SimplifyTautologyTernaryRector extends AbstractRector
{
    /**
     * @var BinaryOpManipulator
     */
    private $binaryOpManipulator;

    public function __construct(BinaryOpManipulator $binaryOpManipulator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Simplify tautology ternary to value', [
            new CodeSample(
                '$value = ($fullyQualifiedTypeHint !== $typeHint) ? $fullyQualifiedTypeHint : $typeHint;',
                '$value = $fullyQualifiedTypeHint;'
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Ternary::class];
    }

    /**
     * @param Ternary $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->cond instanceof NotIdentical && ! $node->cond instanceof Identical) {
            return null;
        }

        $twoNodeMatch = $this->binaryOpManipulator->matchFirstAndSecondConditionNode(
            $node->cond,
            function (Node $leftNode) use ($node): bool {
                return $this->areNodesEqual($leftNode, $node->if);
            },
            function (Node $leftNode) use ($node): bool {
                return $this->areNodesEqual($leftNode, $node->else);
            }
        );

        if ($twoNodeMatch === null) {
            return null;
        }

        return $node->if;
    }
}
