<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\BinaryOp;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Rector\NodeAnalyzer\ExprAnalyzer;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\BinaryOp\RemoveRedundantTypeCheckRector\RemoveRedundantTypeCheckRectorTest
 */
final class RemoveRedundantTypeCheckRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private ExprAnalyzer $exprAnalyzer;
    public function __construct(ValueResolver $valueResolver, ExprAnalyzer $exprAnalyzer)
    {
        $this->valueResolver = $valueResolver;
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove is_<type>() check that can never fail on already known type', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(?string $value, array $items)
    {
        if ($value === null || ! is_string($value)) {
            return;
        }

        if ($items && is_array($items)) {
            return;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(?string $value, array $items)
    {
        if ($value === null) {
            return;
        }

        if ($items) {
            return;
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
        return [BooleanOr::class, BooleanAnd::class];
    }
    /**
     * @param BooleanOr|BooleanAnd $node
     */
    public function refactor(Node $node): ?Expr
    {
        if ($node instanceof BooleanOr) {
            return $this->refactorBooleanOr($node);
        }
        return $this->refactorBooleanAnd($node);
    }
    /**
     * Handles "null === $value || ! is_string($value)", where is_string() can never fail once null is excluded
     */
    private function refactorBooleanOr(BooleanOr $booleanOr): ?Expr
    {
        if (!$booleanOr->left instanceof Identical) {
            return null;
        }
        $nullComparedExpr = $this->matchNullComparedExpr($booleanOr->left);
        if (!$nullComparedExpr instanceof Variable) {
            return null;
        }
        // the docblock type can be wider than the real value
        if ($this->exprAnalyzer->isNonTypedFromParam($nullComparedExpr)) {
            return null;
        }
        if (!$booleanOr->right instanceof BooleanNot) {
            return null;
        }
        $funcCall = $this->matchTypeCheckFuncCall($booleanOr->right->expr, $nullComparedExpr);
        if (!$funcCall instanceof FuncCall) {
            return null;
        }
        $funcCallName = $this->getName($funcCall);
        if ($funcCallName === null) {
            return null;
        }
        $comparedType = $this->getNativeType($nullComparedExpr);
        if (!TypeCombinator::containsNull($comparedType)) {
            return null;
        }
        if (!$this->isAlwaysMatchingType($funcCallName, TypeCombinator::removeNull($comparedType))) {
            return null;
        }
        return $booleanOr->left;
    }
    /**
     * Handles "$items && is_array($items)", where is_array() can never fail on an array type
     */
    private function refactorBooleanAnd(BooleanAnd $booleanAnd): ?Expr
    {
        if (!$booleanAnd->left instanceof Variable) {
            return null;
        }
        // the docblock type can be wider than the real value
        if ($this->exprAnalyzer->isNonTypedFromParam($booleanAnd->left)) {
            return null;
        }
        $funcCall = $this->matchTypeCheckFuncCall($booleanAnd->right, $booleanAnd->left);
        if (!$funcCall instanceof FuncCall) {
            return null;
        }
        $funcCallName = $this->getName($funcCall);
        if ($funcCallName === null) {
            return null;
        }
        // the type is already narrowed by the truthy check on the left
        $checkedType = $this->getNativeType($funcCall->getArgs()[0]->value);
        if (!$this->isAlwaysMatchingType($funcCallName, $checkedType)) {
            return null;
        }
        return $booleanAnd->left;
    }
    /**
     * Matches "is_<type>($expectedExpr)" single arg function call
     */
    private function matchTypeCheckFuncCall(Expr $expr, Expr $expectedExpr): ?FuncCall
    {
        if (!$expr instanceof FuncCall) {
            return null;
        }
        if ($expr->isFirstClassCallable()) {
            return null;
        }
        if (count($expr->getArgs()) !== 1) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($expr->getArgs()[0]->value, $expectedExpr)) {
            return null;
        }
        return $expr;
    }
    private function matchNullComparedExpr(Identical $identical): ?Expr
    {
        if ($this->valueResolver->isNull($identical->left)) {
            return $identical->right;
        }
        if ($this->valueResolver->isNull($identical->right)) {
            return $identical->left;
        }
        return null;
    }
    private function isAlwaysMatchingType(string $funcCallName, Type $type): bool
    {
        switch ($funcCallName) {
            case 'is_string':
                return $type->isString()->yes();
            case 'is_int':
            case 'is_integer':
            case 'is_long':
                return $type->isInteger()->yes();
            case 'is_float':
            case 'is_double':
                return $type->isFloat()->yes();
            case 'is_bool':
                return $type->isBoolean()->yes();
            case 'is_array':
                return $type->isArray()->yes();
            case 'is_object':
                return $type->isObject()->yes();
            default:
                return \false;
        }
    }
}
