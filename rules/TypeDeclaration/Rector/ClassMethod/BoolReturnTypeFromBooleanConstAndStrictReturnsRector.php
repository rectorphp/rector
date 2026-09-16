<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use Rector\NodeAnalyzer\ExprAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\BoolReturnTypeFromBooleanConstAndStrictReturnsRector\BoolReturnTypeFromBooleanConstAndStrictReturnsRectorTest
 */
final class BoolReturnTypeFromBooleanConstAndStrictReturnsRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     */
    private ReturnAnalyzer $returnAnalyzer;
    /**
     * @readonly
     */
    private ExprAnalyzer $exprAnalyzer;
    public function __construct(ValueResolver $valueResolver, BetterNodeFinder $betterNodeFinder, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ReturnAnalyzer $returnAnalyzer, ExprAnalyzer $exprAnalyzer)
    {
        $this->valueResolver = $valueResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->returnAnalyzer = $returnAnalyzer;
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add bool return type when returns mix direct true/false consts with strict bool expressions', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function resolve($value)
    {
        if ($value === []) {
            return true;
        }

        return count($value) > 0;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function resolve($value): bool
    {
        if ($value === []) {
            return true;
        }

        return count($value) > 0;
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
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        if ($this->shouldSkip($node, $scope)) {
            return null;
        }
        $returns = $this->betterNodeFinder->findReturnsScoped($node);
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($node, $returns)) {
            return null;
        }
        if (!$this->hasMixedBoolConstAndStrictReturns($returns)) {
            return null;
        }
        $node->returnType = new Identifier('bool');
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    private function shouldSkip(Node $node, Scope $scope): bool
    {
        // already has the type, skip
        if ($node->returnType instanceof Node) {
            return \true;
        }
        return $node instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope);
    }
    /**
     * Every return must be bool, mixing at least one true/false const with at least one strict bool expression.
     * Pure-const returns are handled by BoolReturnTypeFromBooleanConstReturnsRector, pure-strict by
     * BoolReturnTypeFromBooleanStrictReturnsRector, so this rule stays disjoint from both.
     *
     * @param Return_[] $returns
     */
    private function hasMixedBoolConstAndStrictReturns(array $returns): bool
    {
        $hasConstReturn = \false;
        $hasStrictReturn = \false;
        foreach ($returns as $return) {
            if (!$return->expr instanceof Expr) {
                return \false;
            }
            if ($this->isBoolConst($return->expr)) {
                $hasConstReturn = \true;
                continue;
            }
            if ($this->isStrictBool($return->expr)) {
                $hasStrictReturn = \true;
                continue;
            }
            return \false;
        }
        return $hasConstReturn && $hasStrictReturn;
    }
    private function isBoolConst(Expr $expr): bool
    {
        return $expr instanceof ConstFetch && $this->valueResolver->isTrueOrFalse($expr);
    }
    private function isStrictBool(Expr $expr): bool
    {
        return $this->exprAnalyzer->isBoolExpr($expr) || $this->exprAnalyzer->isCallLikeReturnNativeBool($expr);
    }
}
