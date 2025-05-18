<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\BooleanAnd;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\TypeAnalyzer\NullableTypeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\BooleanAnd\BinaryOpNullableToInstanceofRector\BinaryOpNullableToInstanceofRectorTest
 */
final class BinaryOpNullableToInstanceofRector extends AbstractRector
{
    /**
     * @readonly
     */
    private NullableTypeAnalyzer $nullableTypeAnalyzer;
    public function __construct(NullableTypeAnalyzer $nullableTypeAnalyzer)
    {
        $this->nullableTypeAnalyzer = $nullableTypeAnalyzer;
    }
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
        if ($node->left instanceof Assign || $node->right instanceof Assign) {
            return null;
        }
        if ($node instanceof BooleanOr) {
            return $this->processNegationBooleanOr($node);
        }
        return $this->processNullableInstance($node);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BinaryOp\BooleanOr $node
     * @return null|\PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BinaryOp\BooleanOr
     */
    private function processNullableInstance($node)
    {
        $nullableObjectType = $this->nullableTypeAnalyzer->resolveNullableObjectType($node->left);
        $hasChanged = \false;
        if ($nullableObjectType instanceof ObjectType) {
            $node->left = $this->createExprInstanceof($node->left, $nullableObjectType);
            $hasChanged = \true;
        }
        $nullableObjectType = $this->nullableTypeAnalyzer->resolveNullableObjectType($node->right);
        if ($nullableObjectType instanceof ObjectType) {
            $node->right = $this->createExprInstanceof($node->right, $nullableObjectType);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function processNegationBooleanOr(BooleanOr $booleanOr) : ?BooleanOr
    {
        $hasChanged = \false;
        if ($booleanOr->left instanceof BooleanNot) {
            $nullableObjectType = $this->nullableTypeAnalyzer->resolveNullableObjectType($booleanOr->left->expr);
            if ($nullableObjectType instanceof ObjectType) {
                $booleanOr->left->expr = $this->createExprInstanceof($booleanOr->left->expr, $nullableObjectType);
                $hasChanged = \true;
            }
        }
        if ($booleanOr->right instanceof BooleanNot) {
            $nullableObjectType = $this->nullableTypeAnalyzer->resolveNullableObjectType($booleanOr->right->expr);
            if ($nullableObjectType instanceof ObjectType) {
                $booleanOr->right->expr = $this->createExprInstanceof($booleanOr->right->expr, $nullableObjectType);
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $booleanOr;
        }
        /** @var BooleanOr|null $result */
        $result = $this->processNullableInstance($booleanOr);
        return $result;
    }
    private function createExprInstanceof(Expr $expr, ObjectType $objectType) : Instanceof_
    {
        $fullyQualified = new FullyQualified($objectType->getClassName());
        return new Instanceof_($expr, $fullyQualified);
    }
}
