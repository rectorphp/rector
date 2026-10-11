<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\Instanceof_;
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
        return [If_::class, ElseIf_::class, Ternary::class, Identical::class, NotIdentical::class, Empty_::class, BooleanNot::class, While_::class, Do_::class];
    }
    /**
     * @param If_|ElseIf_|Ternary|Identical|NotIdentical|Empty_|BooleanNot|While_|Do_ $node
     */
    public function refactor(Node $node): ?Node
    {
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
}
