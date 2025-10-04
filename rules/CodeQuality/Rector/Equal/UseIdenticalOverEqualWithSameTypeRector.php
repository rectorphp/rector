<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Equal;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector\UseIdenticalOverEqualWithSameTypeRectorTest
 */
final class UseIdenticalOverEqualWithSameTypeRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Use ===/!== over ==/!=, it values have the same type', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $firstValue, int $secondValue)
    {
         $isSame = $firstValue == $secondValue;
         $isDifferent = $firstValue != $secondValue;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $firstValue, int $secondValue)
    {
         $isSame = $firstValue === $secondValue;
         $isDifferent = $firstValue !== $secondValue;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Equal::class, NotEqual::class];
    }
    /**
     * @param Equal|NotEqual $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->left instanceof ArrayDimFetch || $node->right instanceof ArrayDimFetch) {
            return null;
        }
        $leftStaticType = $this->nodeTypeResolver->getNativeType($node->left);
        $rightStaticType = $this->nodeTypeResolver->getNativeType($node->right);
        // objects can be different by content
        if ($this->hasObjectType($leftStaticType) || $this->hasObjectType($rightStaticType)) {
            return null;
        }
        if ($leftStaticType instanceof MixedType || $rightStaticType instanceof MixedType) {
            return null;
        }
        $normalizedLeftType = $this->normalizeScalarType($leftStaticType);
        $normalizedRightType = $this->normalizeScalarType($rightStaticType);
        if (!$normalizedLeftType->equals($normalizedRightType)) {
            return null;
        }
        return $this->processIdenticalOrNotIdentical($node);
    }
    private function hasObjectType(Type $type): bool
    {
        $hasObjecType = \false;
        TypeTraverser::map($type, function (Type $type, callable $traverseCallback) use (&$hasObjecType): Type {
            // maybe has object type? mark as object type
            if (!$type->isObject()->no()) {
                $hasObjecType = \true;
            }
            return $traverseCallback($type);
        });
        return $hasObjecType;
    }
    private function normalizeScalarType(Type $type): Type
    {
        if ($type->isString()->yes()) {
            return new StringType();
        }
        if ($type->isBoolean()->yes()) {
            return new BooleanType();
        }
        if ($type->isInteger()->yes()) {
            return new IntegerType();
        }
        if ($type->isFloat()->yes()) {
            return new FloatType();
        }
        return $type;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Equal|\PhpParser\Node\Expr\BinaryOp\NotEqual $node
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical
     */
    private function processIdenticalOrNotIdentical($node)
    {
        if ($node instanceof Equal) {
            return new Identical($node->left, $node->right);
        }
        return new NotIdentical($node->left, $node->right);
    }
}
