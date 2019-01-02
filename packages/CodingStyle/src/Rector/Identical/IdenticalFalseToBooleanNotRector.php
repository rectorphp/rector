<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\PhpParser\Node\Maintainer\BinaryOpMaintainer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class IdenticalFalseToBooleanNotRector extends AbstractRector
{
    /**
     * @var BinaryOpMaintainer
     */
    private $binaryOpMaintainer;

    public function __construct(BinaryOpMaintainer $binaryOpMaintainer)
    {
        $this->binaryOpMaintainer = $binaryOpMaintainer;
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
        $matchedNodes = $this->binaryOpMaintainer->matchFirstAndSecondConditionNode(
            $node,
            function (Node $node) {
                return ! $node instanceof BinaryOp;
            },
            function (Node $node) {
                return $this->isFalse($node);
            }
        );

        if ($matchedNodes === null) {
            return null;
        }

        /** @var Expr $comparedNode */
        [$comparedNode, ] = $matchedNodes;

        if ($this->hasNullOrIntegerType($this->getStaticType($comparedNode))) {
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
     */
    private function hasNullOrIntegerType(?Type $staticType): bool
    {
        if ($staticType instanceof UnionType) {
            return $staticType->isSuperTypeOf(new IntegerType())->yes() && $staticType->isSuperTypeOf(
                new ConstantBooleanType(false)
            )->yes();
        }

        return false;
    }
}
