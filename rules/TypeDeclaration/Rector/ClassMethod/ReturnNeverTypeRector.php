<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Expr\YieldFrom;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use PHPStan\Analyser\Scope;
use Rector\NodeNestingScope\ValueObject\ControlStructure;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\Reflection\ClassModifierChecker;
use Rector\TypeDeclaration\NodeAnalyzer\NeverFuncCallAnalyzer;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/noreturn_type
 *
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\ReturnNeverTypeRectorTest
 */
final class ReturnNeverTypeRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\NeverFuncCallAnalyzer
     */
    private $neverFuncCallAnalyzer;
    /**
     * @readonly
     * @var \Rector\Reflection\ClassModifierChecker
     */
    private $classModifierChecker;
    public function __construct(ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, BetterNodeFinder $betterNodeFinder, NeverFuncCallAnalyzer $neverFuncCallAnalyzer, ClassModifierChecker $classModifierChecker)
    {
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->neverFuncCallAnalyzer = $neverFuncCallAnalyzer;
        $this->classModifierChecker = $classModifierChecker;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add "never" return-type for methods that never return anything', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        throw new InvalidException();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): never
    {
        throw new InvalidException();
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
        return [ClassMethod::class, Function_::class, Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if ($this->shouldSkip($node, $scope)) {
            return null;
        }
        $node->returnType = new Identifier('never');
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NEVER_TYPE;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    private function shouldSkip($node, Scope $scope) : bool
    {
        if ($node->returnType instanceof Node && !$this->isName($node->returnType, 'void')) {
            return \true;
        }
        if ($this->hasReturnOrYields($node)) {
            return \true;
        }
        if (!$this->hasNeverNodesOrNeverFuncCalls($node)) {
            return \true;
        }
        if ($node instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return \true;
        }
        if (!$node->returnType instanceof Node) {
            return \false;
        }
        // skip as most likely intentional
        if (!$this->classModifierChecker->isInsideFinalClass($node) && $this->isName($node->returnType, 'void')) {
            return \true;
        }
        return $this->isName($node->returnType, 'never');
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    private function hasReturnOrYields($node) : bool
    {
        if ($this->betterNodeFinder->hasInstancesOfInFunctionLikeScoped($node, Return_::class)) {
            return \true;
        }
        return $this->betterNodeFinder->hasInstancesOfInFunctionLikeScoped($node, \array_merge([Yield_::class, YieldFrom::class], ControlStructure::CONDITIONAL_NODE_SCOPE_TYPES));
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    private function hasNeverNodesOrNeverFuncCalls($node) : bool
    {
        $hasNeverNodes = $this->betterNodeFinder->hasInstancesOfInFunctionLikeScoped($node, [Throw_::class]);
        if ($hasNeverNodes) {
            return \true;
        }
        return $this->neverFuncCallAnalyzer->hasNeverFuncCall($node);
    }
}
