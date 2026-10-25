<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\While_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\CodeQuality\NodeAnalyzer\ExplicitBoolConditionResolver;
use Rector\CodeQuality\ValueObject\ExplicitBoolCondition;
use Rector\PhpParser\Node\NullableObjectToInstanceofFactory;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\TypeDeclaration\TypeAnalyzer\NullableTypeAnalyzer;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\ExplicitNullableObjectInstanceRector\ExplicitNullableObjectInstanceRectorTest
 */
final class ExplicitNullableObjectInstanceRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ExplicitBoolConditionResolver $explicitBoolConditionResolver;
    /**
     * @readonly
     */
    private NullableTypeAnalyzer $nullableTypeAnalyzer;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private NullableObjectToInstanceofFactory $nullableObjectToInstanceofFactory;
    public function __construct(ExplicitBoolConditionResolver $explicitBoolConditionResolver, NullableTypeAnalyzer $nullableTypeAnalyzer, ValueResolver $valueResolver, NullableObjectToInstanceofFactory $nullableObjectToInstanceofFactory)
    {
        $this->explicitBoolConditionResolver = $explicitBoolConditionResolver;
        $this->nullableTypeAnalyzer = $nullableTypeAnalyzer;
        $this->valueResolver = $valueResolver;
        $this->nullableObjectToInstanceofFactory = $nullableObjectToInstanceofFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Make implicit nullable object checks explicit with instanceof', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeController
{
    public function run(?\stdClass $item)
    {
        if (!$item) {
            return 'empty';
        }

        if ($item === null) {
            return 'empty';
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeController
{
    public function run(?\stdClass $item)
    {
        if (!$item instanceof \stdClass) {
            return 'empty';
        }

        if (!$item instanceof \stdClass) {
            return 'empty';
        }
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
        return [If_::class, ElseIf_::class, Ternary::class, Identical::class, NotIdentical::class, Empty_::class, BooleanNot::class, While_::class, Do_::class, BooleanAnd::class, BooleanOr::class];
    }
    /**
     * @param If_|ElseIf_|Ternary|Identical|NotIdentical|Empty_|BooleanNot|While_|Do_|BooleanAnd|BooleanOr $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof BooleanAnd || $node instanceof BooleanOr) {
            return $this->refactorBinaryOp($node);
        }
        if ($node instanceof Identical || $node instanceof NotIdentical) {
            return $this->refactorNullCompare($node);
        }
        if ($node instanceof Empty_ || $node instanceof BooleanNot) {
            return $this->refactorEmpty($node);
        }
        if ($node instanceof While_ || $node instanceof Do_) {
            return $this->refactorLoop($node);
        }
        if ($node instanceof If_ || $node instanceof ElseIf_ || $node instanceof Ternary) {
            return $this->refactorExplicitBool($node);
        }
        return null;
    }
    /**
     * @template TNode of If_|ElseIf_|Ternary
     * @param \PhpParser\Node\Stmt\If_|\PhpParser\Node\Stmt\ElseIf_|\PhpParser\Node\Expr\Ternary $node
     * @return TNode|null
     */
    private function refactorExplicitBool($node)
    {
        $explicitBoolCondition = $this->explicitBoolConditionResolver->resolve($node);
        if (!$explicitBoolCondition instanceof ExplicitBoolCondition) {
            return null;
        }
        $expr = $explicitBoolCondition->getConditionNode();
        $objectType = $this->nodeTypeResolver->matchNullableTypeOfSpecificType($expr, ObjectType::class);
        if (!$objectType instanceof ObjectType) {
            return null;
        }
        $node->cond = $this->nullableObjectToInstanceofFactory->create($expr, $objectType, $explicitBoolCondition->isNegated());
        return $node;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical $node
     * @return \PhpParser\Node\Expr\BooleanNot|\PhpParser\Node\Expr\Instanceof_|null
     */
    private function refactorNullCompare($node)
    {
        $expr = $this->matchNullComparedExpr($node);
        if (!$expr instanceof Expr) {
            return null;
        }
        $nullableObjectType = $this->nullableTypeAnalyzer->resolveNullableObjectType($expr);
        if (!$nullableObjectType instanceof ObjectType) {
            return null;
        }
        return $this->nullableObjectToInstanceofFactory->create($expr, $nullableObjectType, $node instanceof Identical);
    }
    /**
     * @param \PhpParser\Node\Expr\Empty_|\PhpParser\Node\Expr\BooleanNot $node
     * @return \PhpParser\Node\Expr\BooleanNot|\PhpParser\Node\Expr\Instanceof_|null
     */
    private function refactorEmpty($node)
    {
        if ($node instanceof BooleanNot) {
            if (!$node->expr instanceof Empty_) {
                return null;
            }
            $isNegated = \true;
            $empty = $node->expr;
        } else {
            $empty = $node;
            $isNegated = \false;
        }
        if ($empty->expr instanceof ArrayDimFetch) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        $exprType = $scope->getNativeType($empty->expr);
        if (!$exprType instanceof UnionType) {
            return null;
        }
        $exprType = TypeCombinator::removeNull($exprType);
        if (!$exprType instanceof ObjectType) {
            return null;
        }
        return $this->nullableObjectToInstanceofFactory->create($empty->expr, $exprType, !$isNegated);
    }
    /**
     * @template TLoop of While_|Do_
     * @param \PhpParser\Node\Stmt\While_|\PhpParser\Node\Stmt\Do_ $node
     * @return TLoop|null
     */
    private function refactorLoop($node)
    {
        if ($node->cond instanceof Assign) {
            return null;
        }
        // "!== null" conditions are handled by the NotIdentical node type
        if ($node->cond instanceof NotIdentical || $node->cond instanceof Identical) {
            return null;
        }
        $condNullableObjectType = $this->nullableTypeAnalyzer->resolveNullableObjectType($node->cond);
        if (!$condNullableObjectType instanceof ObjectType) {
            return null;
        }
        $node->cond = $this->nullableObjectToInstanceofFactory->create($node->cond, $condNullableObjectType, \false);
        return $node;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical $binaryOp
     */
    private function matchNullComparedExpr($binaryOp): ?Expr
    {
        if ($this->valueResolver->isNull($binaryOp->left)) {
            return $binaryOp->right;
        }
        if ($this->valueResolver->isNull($binaryOp->right)) {
            return $binaryOp->left;
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BinaryOp\BooleanOr $node
     */
    private function refactorBinaryOp($node): ?Node
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
     * @template TBinaryOp of BooleanAnd|BooleanOr
     * @param \PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BinaryOp\BooleanOr $node
     * @return TBinaryOp|null
     */
    private function processNullableInstance($node)
    {
        $hasChanged = \false;
        $leftNullableObjectType = $this->resolveNullableObjectType($node->left);
        if ($leftNullableObjectType instanceof ObjectType) {
            $node->left = $this->nullableObjectToInstanceofFactory->create($node->left, $leftNullableObjectType, \false);
            $hasChanged = \true;
        }
        $rightNullableObjectType = $this->resolveNullableObjectType($node->right);
        if ($rightNullableObjectType instanceof ObjectType) {
            $node->right = $this->nullableObjectToInstanceofFactory->create($node->right, $rightNullableObjectType, \false);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function processNegationBooleanOr(BooleanOr $booleanOr): ?BooleanOr
    {
        $hasChanged = \false;
        if ($booleanOr->left instanceof BooleanNot) {
            $nullableObjectType = $this->resolveNullableObjectType($booleanOr->left->expr);
            if ($nullableObjectType instanceof ObjectType) {
                $booleanOr->left->expr = $this->nullableObjectToInstanceofFactory->create($booleanOr->left->expr, $nullableObjectType, \false);
                $hasChanged = \true;
            }
        }
        if ($booleanOr->right instanceof BooleanNot) {
            $nullableObjectType = $this->resolveNullableObjectType($booleanOr->right->expr);
            if ($nullableObjectType instanceof ObjectType) {
                $booleanOr->right->expr = $this->nullableObjectToInstanceofFactory->create($booleanOr->right->expr, $nullableObjectType, \false);
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $booleanOr;
        }
        return $this->processNullableInstance($booleanOr);
    }
    private function resolveNullableObjectType(Expr $expr): ?ObjectType
    {
        // cheap bail - bool/scalar-shaped operands can never be a nullable object,
        // skip the costly native type resolution for them (e.g. chained "$a && $b && $c")
        if ($this->isNeverNullableObject($expr)) {
            return null;
        }
        return $this->nullableTypeAnalyzer->resolveNullableObjectType($expr);
    }
    private function isNeverNullableObject(Expr $expr): bool
    {
        return $expr instanceof BinaryOp || $expr instanceof BooleanNot || $expr instanceof Instanceof_ || $expr instanceof Empty_ || $expr instanceof Isset_;
    }
}
