<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Rector\ValueObject\PhpVersion;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\StringReturnTypeFromStrictStringReturnsRector\StringReturnTypeFromStrictStringReturnsRectorTest
 */
final class StringReturnTypeFromStrictStringReturnsRector extends AbstractRector implements MinPhpVersionInterface
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
        return new RuleDefinition('Add string return type based on returned strict string values', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function foo($condition, $value)
    {
        if ($value) {
            return 'yes';
        }

        return strtoupper($value);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function foo($condition, $value): string;
    {
        if ($value) {
            return 'yes';
        }

        return strtoupper($value);
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
        // already added → skip
        if ($node->returnType instanceof Node) {
            return null;
        }
        $returns = $this->betterNodeFinder->findReturnsScoped($node);
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($node, $returns)) {
            return null;
        }
        // handled by another rule
        if ($this->hasAlwaysStringScalarReturn($returns)) {
            return null;
        }
        // anything that return strict string, but no strings only
        if (!$this->isAlwaysStringStrictType($returns)) {
            return null;
        }
        if ($this->shouldSkipClassMethodForOverride($node, $scope)) {
            return null;
        }
        $node->returnType = new Identifier('string');
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_70;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function shouldSkipClassMethodForOverride($functionLike, Scope $scope) : bool
    {
        if (!$functionLike instanceof ClassMethod) {
            return \false;
        }
        return $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($functionLike, $scope);
    }
    /**
     * @param Return_[] $returns
     */
    private function hasAlwaysStringScalarReturn(array $returns) : bool
    {
        foreach ($returns as $return) {
            // we need exact string "value" return
            if (!$return->expr instanceof String_ && !$return->expr instanceof InterpolatedString) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * @param Return_[] $returns
     */
    private function isAlwaysStringStrictType(array $returns) : bool
    {
        foreach ($returns as $return) {
            // void return
            if (!$return->expr instanceof Expr) {
                return \false;
            }
            $exprType = $this->nodeTypeResolver->getNativeType($return->expr);
            if (!$exprType->isString()->yes()) {
                return \false;
            }
        }
        return \true;
    }
}
