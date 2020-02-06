<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\PhpParser\Node\Manipulator\BinaryOpManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Identical\IdenticalFalseToBooleanNotRector\IdenticalFalseToBooleanNotRectorTest
 */
final class IdenticalFalseToBooleanNotRector extends AbstractRector
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
        return new RectorDefinition('Changes === false to negate !', [
            new CodeSample('if ($something === false) {}', 'if (! $something) {}'),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Identical::class];
    }

    /**
     * @param Identical $node
     */
    public function refactor(Node $node): ?Node
    {
        $matchedNodes = $this->binaryOpManipulator->matchFirstAndSecondConditionNode(
            $node,
            function (Node $node): bool {
                return ! $node instanceof BinaryOp;
            },
            function (Node $node): bool {
                return $this->isFalse($node);
            }
        );

        if ($matchedNodes === null) {
            return null;
        }

        /** @var Expr $comparedNode */
        [$comparedNode, ] = $matchedNodes;

        $staticType = $this->getStaticType($comparedNode);

        // nothing we can do
        if ($staticType instanceof MixedType) {
            return null;
        }

        if ($this->hasNullOrIntegerType($staticType)) {
            return null;
        }

        if ($comparedNode instanceof BooleanNot) {
            return $comparedNode->expr;
        }

        return new BooleanNot($comparedNode);
    }

    /**
     * E.g strpos() can return 0 and false, so this would be false positive:
     * ! 0 → true
     * ! false → true
     * ! mixed → true/false
     */
    private function hasNullOrIntegerType(?Type $staticType): bool
    {
        if (! $staticType instanceof UnionType) {
            return false;
        }

        foreach ($staticType->getTypes() as $unionedType) {
            if ($unionedType instanceof NullType) {
                return true;
            }

            if ($unionedType instanceof IntegerType) {
                return true;
            }
        }

        return false;
    }
}
