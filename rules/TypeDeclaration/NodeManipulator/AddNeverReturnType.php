<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Expr\YieldFrom;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNestingScope\ValueObject\ControlStructure;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Reflection\ClassModifierChecker;
use Rector\TypeDeclaration\NodeAnalyzer\NeverFuncCallAnalyzer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
final class AddNeverReturnType
{
    /**
     * @readonly
     */
    private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     */
    private ClassModifierChecker $classModifierChecker;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private NeverFuncCallAnalyzer $neverFuncCallAnalyzer;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ClassModifierChecker $classModifierChecker, BetterNodeFinder $betterNodeFinder, NeverFuncCallAnalyzer $neverFuncCallAnalyzer, NodeNameResolver $nodeNameResolver)
    {
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->classModifierChecker = $classModifierChecker;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->neverFuncCallAnalyzer = $neverFuncCallAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|null
     */
    public function add($node, Scope $scope)
    {
        if ($this->shouldSkip($node, $scope)) {
            return null;
        }
        $node->returnType = new Identifier('never');
        return $node;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    private function shouldSkip($node, Scope $scope) : bool
    {
        // already has return type, and non-void
        // it can be "never" return itself, or other return type
        if ($node->returnType instanceof Node && !$this->nodeNameResolver->isName($node->returnType, 'void')) {
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
        return !$this->classModifierChecker->isInsideFinalClass($node) && $this->nodeNameResolver->isName($node->returnType, 'void');
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    private function hasReturnOrYields($node) : bool
    {
        return $this->betterNodeFinder->hasInstancesOfInFunctionLikeScoped($node, \array_merge([Return_::class, Yield_::class, YieldFrom::class], ControlStructure::CONDITIONAL_NODE_SCOPE_TYPES));
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    private function hasNeverNodesOrNeverFuncCalls($node) : bool
    {
        $hasNeverNodes = (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($node, fn(Node $subNode): bool => $subNode instanceof Expression && $subNode->expr instanceof Throw_);
        if ($hasNeverNodes) {
            return \true;
        }
        return $this->neverFuncCallAnalyzer->hasNeverFuncCall($node);
    }
}
