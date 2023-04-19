<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\BooleanAnd;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\BooleanAnd\BinaryOpNullableToInstanceofRector\BinaryOpNullableToInstanceofRectorTest
 */
final class BinaryOpNullableToInstanceofRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change && and || between nullable objects to instanceof compares', [new CodeSample(<<<'CODE_SAMPLE'
function someFunction(?SomeClass $someClass)
{
    if ($someClass && $someClass->someMethod()) {
        return 'yes';
    }

    return 'no';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function someFunction(?SomeClass $someClass)
{
    if ($someClass instanceof SomeClass && $someClass->someMethod()) {
        return 'yes';
    }

    return 'no';
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [BooleanAnd::class, BooleanOr::class];
    }
    /**
     * @param BooleanAnd|BooleanOr $node
     */
    public function refactor(Node $node) : ?Node
    {
        $nullableObjectType = $this->returnNullableObjectType($node->left);
        if ($nullableObjectType instanceof ObjectType) {
            $node->left = $this->createExprInstanceof($node->left, $nullableObjectType);
            return $node;
        }
        $nullableObjectType = $this->returnNullableObjectType($node->right);
        if ($nullableObjectType instanceof ObjectType) {
            $node->right = $this->createExprInstanceof($node->right, $nullableObjectType);
            return $node;
        }
        return null;
    }
    private function returnNullableObjectType(Expr $expr) : ?\PHPStan\Type\ObjectType
    {
        $exprType = $this->getType($expr);
        $baseType = TypeCombinator::removeNull($exprType);
        if (!$baseType instanceof ObjectType) {
            return null;
        }
        return $baseType;
    }
    private function createExprInstanceof(Expr $expr, ObjectType $objectType) : Instanceof_
    {
        $fullyQualified = new FullyQualified($objectType->getClassName());
        return new Instanceof_($expr, $fullyQualified);
    }
}
