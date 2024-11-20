<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitor;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
final class AddReturnTypeFromParam
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;
    /**
     * @readonly
     */
    private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     */
    private ReturnTypeInferer $returnTypeInferer;
    public function __construct(NodeNameResolver $nodeNameResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ReturnTypeInferer $returnTypeInferer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->returnTypeInferer = $returnTypeInferer;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|null
     */
    public function add($functionLike, Scope $scope)
    {
        if ($functionLike->stmts === null) {
            return null;
        }
        if ($this->shouldSkipNode($functionLike, $scope)) {
            return null;
        }
        $return = $this->findCurrentScopeReturn($functionLike->stmts);
        if (!$return instanceof Return_ || !$return->expr instanceof Expr) {
            return null;
        }
        $returnName = $this->nodeNameResolver->getName($return->expr);
        $stmts = $functionLike->stmts;
        foreach ($functionLike->getParams() as $param) {
            if (!$param->type instanceof Node) {
                continue;
            }
            if ($this->shouldSkipParam($param, $stmts)) {
                continue;
            }
            $paramName = $this->nodeNameResolver->getName($param);
            if ($returnName !== $paramName) {
                continue;
            }
            $functionLike->returnType = $param->type;
            return $functionLike;
        }
        return null;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function findCurrentScopeReturn(array $stmts) : ?Return_
    {
        $return = null;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, static function (Node $node) use(&$return) : ?int {
            // skip scope nesting
            if ($node instanceof Class_ || $node instanceof FunctionLike) {
                $return = null;
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof Return_) {
                return null;
            }
            if (!$node->expr instanceof Variable) {
                $return = null;
                return NodeVisitor::STOP_TRAVERSAL;
            }
            $return = $node;
            return null;
        });
        return $return;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function shouldSkipParam(Param $param, array $stmts) : bool
    {
        $paramName = $this->nodeNameResolver->getName($param);
        $isParamModified = \false;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use($paramName, &$isParamModified) : ?int {
            // skip scope nesting
            if ($node instanceof Class_ || $node instanceof FunctionLike) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($node instanceof AssignRef && $this->nodeNameResolver->isName($node->expr, $paramName)) {
                $isParamModified = \true;
                return NodeVisitor::STOP_TRAVERSAL;
            }
            if (!$node instanceof Assign) {
                return null;
            }
            if (!$node->var instanceof Variable) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->var, $paramName)) {
                return null;
            }
            $isParamModified = \true;
            return NodeVisitor::STOP_TRAVERSAL;
        });
        return $isParamModified;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function shouldSkipNode($functionLike, Scope $scope) : bool
    {
        // type is already known, skip
        if ($functionLike->returnType instanceof Node) {
            return \true;
        }
        if ($functionLike instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($functionLike, $scope)) {
            return \true;
        }
        $returnType = $this->returnTypeInferer->inferFunctionLike($functionLike);
        if ($returnType instanceof MixedType) {
            return \true;
        }
        $returnType = TypeCombinator::removeNull($returnType);
        return $returnType instanceof UnionType;
    }
}
