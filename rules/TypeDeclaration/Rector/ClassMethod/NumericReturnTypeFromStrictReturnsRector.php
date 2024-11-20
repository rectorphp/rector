<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\NumericReturnTypeFromStrictReturnsRector\NumericReturnTypeFromStrictReturnsRectorTest
 */
final class NumericReturnTypeFromStrictReturnsRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private ReturnAnalyzer $returnAnalyzer;
    public function __construct(ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, BetterNodeFinder $betterNodeFinder, ReturnAnalyzer $returnAnalyzer)
    {
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->returnAnalyzer = $returnAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add int/float return type based on strict typed returns', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function increase($value)
    {
        return ++$value;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function increase($value): int
    {
        return ++$value;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        if ($this->shouldSkip($node, $scope)) {
            return null;
        }
        $returns = $this->betterNodeFinder->findReturnsScoped($node);
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($node, $returns)) {
            return null;
        }
        // handled by another rule
        if ($this->isAlwaysNumeric($returns)) {
            return null;
        }
        $isAlwaysIntType = \true;
        $isAlwaysFloatType = \true;
        foreach ($returns as $return) {
            if (!$return->expr instanceof Expr) {
                return null;
            }
            $exprType = $this->nodeTypeResolver->getNativeType($return->expr);
            if (!$exprType->isInteger()->yes()) {
                $isAlwaysIntType = \false;
            }
            if (!$exprType->isFloat()->yes()) {
                $isAlwaysFloatType = \false;
            }
        }
        if ($isAlwaysFloatType) {
            $node->returnType = new Identifier('float');
            return $node;
        }
        if ($isAlwaysIntType) {
            $node->returnType = new Identifier('int');
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function shouldSkip($functionLike, Scope $scope) : bool
    {
        // type is already known, skip
        if ($functionLike->returnType instanceof Node) {
            return \true;
        }
        // empty, nothing to find
        if ($functionLike->stmts === null || $functionLike->stmts === []) {
            return \true;
        }
        if (!$functionLike instanceof ClassMethod) {
            return \false;
        }
        return $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($functionLike, $scope);
    }
    /**
     * @param Return_[] $returns
     */
    private function isAlwaysNumeric(array $returns) : bool
    {
        $isAlwaysFloat = \true;
        $isAlwaysInt = \true;
        foreach ($returns as $return) {
            $epxr = $return->expr;
            if ($epxr instanceof UnaryMinus) {
                $epxr = $epxr->expr;
            }
            if (!$epxr instanceof Float_) {
                $isAlwaysFloat = \false;
            }
            if (!$epxr instanceof Int_) {
                $isAlwaysInt = \false;
            }
        }
        if ($isAlwaysFloat) {
            return \true;
        }
        return $isAlwaysInt;
    }
}
