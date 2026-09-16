<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\TypeCombinator;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/nullsafe_operator
 *
 * @see \Rector\Tests\Php80\Rector\Ternary\TernaryToNullsafeCoalesceRector\TernaryToNullsafeCoalesceRectorTest
 */
final class TernaryToNullsafeCoalesceRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change null check ternary around a method call or property fetch to nullsafe operator', [new CodeSample(<<<'CODE_SAMPLE'
$value = null !== $dateTime ? $dateTime->format('d/m/Y') : '';
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value = $dateTime?->format('d/m/Y') ?? '';
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
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
        // short ternary "$a ?: $b" has no "if" branch
        if (!$node->if instanceof Expr) {
            return null;
        }
        $checkedExpr = $this->matchNullComparedExpr($node->cond);
        if (!$checkedExpr instanceof Expr) {
            return null;
        }
        if ($node->cond instanceof NotIdentical) {
            // null !== $a ? $a->call() : $fallback
            $callExpr = $node->if;
            $fallbackExpr = $node->else;
        } else {
            // null === $a ? $fallback : $a->call()
            $callExpr = $node->else;
            $fallbackExpr = $node->if;
        }
        // re-evaluating the checked expression must stay free of side effects
        if (!$this->isPureExpr($checkedExpr)) {
            return null;
        }
        $nullsafeExpr = $this->createNullsafeChain($callExpr, $checkedExpr);
        if (!$nullsafeExpr instanceof Expr) {
            return null;
        }
        // "$a !== null ? $a->call() : null" needs no fallback at all
        if ($this->valueResolver->isNull($fallbackExpr)) {
            return $nullsafeExpr;
        }
        if ($this->shouldSkipCoalesceFallback($callExpr)) {
            return null;
        }
        return new Coalesce($nullsafeExpr, $fallbackExpr);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::NULLSAFE_OPERATOR;
    }
    /**
     * Resolves the expression compared against null, for both "null !== $a" and "$a !== null" orders.
     */
    private function matchNullComparedExpr(Expr $expr): ?Expr
    {
        if (!$expr instanceof NotIdentical && !$expr instanceof Identical) {
            return null;
        }
        if ($this->valueResolver->isNull($expr->left)) {
            return $expr->right;
        }
        if ($this->valueResolver->isNull($expr->right)) {
            return $expr->left;
        }
        return null;
    }
    /**
     * Rewrites the deepest link of "$a->b()->c" that is rooted in $checkedExpr into its nullsafe
     * counterpart; the rest of the chain short-circuits on its own.
     */
    private function createNullsafeChain(Expr $expr, Expr $checkedExpr): ?Expr
    {
        if ($expr instanceof MethodCall) {
            if ($this->nodeComparator->areNodesEqual($expr->var, $checkedExpr)) {
                return new NullsafeMethodCall($expr->var, $expr->name, $expr->args);
            }
            $nestedExpr = $this->createNullsafeChain($expr->var, $checkedExpr);
            if (!$nestedExpr instanceof Expr) {
                return null;
            }
            return new MethodCall($nestedExpr, $expr->name, $expr->args);
        }
        if ($expr instanceof PropertyFetch) {
            if ($this->nodeComparator->areNodesEqual($expr->var, $checkedExpr)) {
                return new NullsafePropertyFetch($expr->var, $expr->name);
            }
            $nestedExpr = $this->createNullsafeChain($expr->var, $checkedExpr);
            if (!$nestedExpr instanceof Expr) {
                return null;
            }
            return new PropertyFetch($nestedExpr, $expr->name);
        }
        return null;
    }
    private function isPureExpr(Expr $expr): bool
    {
        if ($expr instanceof Variable) {
            return \true;
        }
        if ($expr instanceof PropertyFetch) {
            return $this->isPureExpr($expr->var);
        }
        return $expr instanceof StaticPropertyFetch;
    }
    /**
     * Guards the "?? $fallback" rewrite.
     *
     * The ternary and the coalesce only agree while the call itself cannot return null:
     *
     *     null !== $a ? $a->find() : ''   // $a->find() returning null yields null
     *     $a?->find() ?? ''               // $a->find() returning null yields ''
     */
    private function shouldSkipCoalesceFallback(Expr $callExpr): bool
    {
        return TypeCombinator::containsNull($this->getType($callExpr));
    }
}
