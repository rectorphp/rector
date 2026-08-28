<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp\Coalesce as AssignCoalesce;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeCombinator;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php74\Rector\If_\IfToNullCoalescingAssignRector\IfToNullCoalescingAssignRectorTest
 */
final class IfToNullCoalescingAssignRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, ValueResolver $valueResolver, ReflectionResolver $reflectionResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->valueResolver = $valueResolver;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change `if` null guard with single assign to null coalescing assign `??=`', [new CodeSample(<<<'CODE_SAMPLE'
if (! isset($array['user_id'])) {
    $array['user_id'] = 'value';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$array['user_id'] ??= 'value';
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Expression
    {
        if ($node->else instanceof Else_) {
            return null;
        }
        if ($node->elseifs !== []) {
            return null;
        }
        if (count($node->stmts) !== 1) {
            return null;
        }
        $onlyStmt = $node->stmts[0];
        if (!$onlyStmt instanceof Expression) {
            return null;
        }
        $assign = $onlyStmt->expr;
        if (!$assign instanceof Assign) {
            return null;
        }
        $testedExpr = $this->matchNullGuardedExpr($node->cond);
        if (!$testedExpr instanceof Expr) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($assign->var, $testedExpr)) {
            return null;
        }
        // a typed non-nullable property can never be null on the left of ??=
        if ($this->isNonNullableProperty($testedExpr)) {
            return null;
        }
        // the assigned value must not reference the target, e.g. $x = $x + 1
        $selfReference = $this->betterNodeFinder->findFirst($assign->expr, fn(Node $subNode): bool => $this->nodeComparator->areNodesEqual($subNode, $assign->var));
        if ($selfReference instanceof Node) {
            return null;
        }
        $expression = new Expression(new AssignCoalesce($assign->var, $assign->expr));
        $this->mirrorComments($expression, $node);
        return $expression;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::NULL_COALESCE_ASSIGN;
    }
    private function isNonNullableProperty(Expr $expr): bool
    {
        if (!$expr instanceof PropertyFetch && !$expr instanceof StaticPropertyFetch) {
            return \false;
        }
        $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($expr);
        if (!$phpPropertyReflection instanceof PhpPropertyReflection) {
            return \false;
        }
        $propertyType = $phpPropertyReflection->getReadableType();
        if ($propertyType instanceof MixedType) {
            return \false;
        }
        return !TypeCombinator::containsNull($propertyType);
    }
    private function matchNullGuardedExpr(Expr $expr): ?Expr
    {
        // ! isset($value)
        if ($expr instanceof BooleanNot && $expr->expr instanceof Isset_) {
            if (count($expr->expr->vars) !== 1) {
                return null;
            }
            return $expr->expr->vars[0];
        }
        // is_null($value)
        if ($expr instanceof FuncCall && $this->isName($expr, 'is_null')) {
            if ($expr->isFirstClassCallable()) {
                return null;
            }
            if (count($expr->getArgs()) !== 1) {
                return null;
            }
            return $expr->getArgs()[0]->value;
        }
        // null === $value or $value === null
        if ($expr instanceof Identical) {
            if ($this->valueResolver->isNull($expr->left)) {
                return $expr->right;
            }
            if ($this->valueResolver->isNull($expr->right)) {
                return $expr->left;
            }
        }
        return null;
    }
}
