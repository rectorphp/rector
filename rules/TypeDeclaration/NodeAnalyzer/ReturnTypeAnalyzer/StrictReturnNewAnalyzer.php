<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\TypeDeclaration\ValueObject\AssignToVariable;
final class StrictReturnNewAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function matchAlwaysReturnVariableNew($functionLike) : ?string
    {
        if ($functionLike->stmts === null) {
            return null;
        }
        if ($this->betterNodeFinder->hasInstancesOfInFunctionLikeScoped($functionLike, [Yield_::class])) {
            return null;
        }
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($functionLike, Return_::class);
        if ($returns === []) {
            return null;
        }
        // is one statement depth 3?
        if (!$this->areExclusiveExprReturns($returns)) {
            return null;
        }
        // has root return?
        if (!$this->hasClassMethodRootReturn($functionLike)) {
            return null;
        }
        if (\count($returns) !== 1) {
            return null;
        }
        // exact one return of variable
        $onlyReturn = $returns[0];
        if (!$onlyReturn->expr instanceof Variable) {
            return null;
        }
        $returnType = $this->nodeTypeResolver->getType($onlyReturn->expr);
        if (!$returnType instanceof ObjectType) {
            return null;
        }
        $createdVariablesToTypes = $this->resolveCreatedVariablesToTypes($functionLike);
        $returnedVariableName = $this->nodeNameResolver->getName($onlyReturn->expr);
        $className = $createdVariablesToTypes[$returnedVariableName] ?? null;
        if (!\is_string($className)) {
            return $className;
        }
        if ($returnType->getClassName() === $className) {
            return $className;
        }
        return null;
    }
    /**
     * @param Return_[] $returns
     */
    private function areExclusiveExprReturns(array $returns) : bool
    {
        foreach ($returns as $return) {
            if (!$return->expr instanceof Expr) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function hasClassMethodRootReturn($functionLike) : bool
    {
        foreach ((array) $functionLike->stmts as $stmt) {
            if ($stmt instanceof Return_) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @return array<string, string>
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function resolveCreatedVariablesToTypes($functionLike) : array
    {
        $createdVariablesToTypes = [];
        // what new is assigned to it?
        foreach ((array) $functionLike->stmts as $stmt) {
            $assignToVariable = $this->matchAssignToVariable($stmt);
            if (!$assignToVariable instanceof AssignToVariable) {
                continue;
            }
            $assignedExpr = $assignToVariable->getAssignedExpr();
            $variableName = $assignToVariable->getVariableName();
            if (!$assignedExpr instanceof New_) {
                // possible variable override by another type! - unset it
                if (isset($createdVariablesToTypes[$variableName])) {
                    unset($createdVariablesToTypes[$variableName]);
                }
                continue;
            }
            $className = $this->nodeNameResolver->getName($assignedExpr->class);
            if (!\is_string($className)) {
                continue;
            }
            $createdVariablesToTypes[$variableName] = $className;
        }
        return $createdVariablesToTypes;
    }
    private function matchAssignToVariable(Stmt $stmt) : ?AssignToVariable
    {
        if (!$stmt instanceof Expression) {
            return null;
        }
        if (!$stmt->expr instanceof Assign) {
            return null;
        }
        $assign = $stmt->expr;
        $assignedVar = $assign->var;
        if (!$assignedVar instanceof Variable) {
            return null;
        }
        $variableName = $this->nodeNameResolver->getName($assignedVar);
        if (!\is_string($variableName)) {
            return null;
        }
        return new AssignToVariable($assignedVar, $variableName, $assign->expr);
    }
}
