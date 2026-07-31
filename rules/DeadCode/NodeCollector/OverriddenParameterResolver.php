<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeCollector;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
/**
 * Resolves parameters, whose value is thrown away by a direct assign, before the parameter is ever read
 */
final class OverriddenParameterResolver
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * Functions that can read a parameter value without naming its variable
     * @var string[]
     */
    private const INDIRECT_VARIABLE_FUNCTION_NAMES = ['compact', 'extract', 'func_get_arg', 'func_get_args', 'func_num_args', 'get_defined_vars'];
    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @return array<int, Param>
     */
    public function resolve(ClassMethod $classMethod): array
    {
        $stmts = $classMethod->stmts;
        if ($stmts === null || $stmts === []) {
            return [];
        }
        if ($this->hasIndirectVariableUsage($classMethod)) {
            return [];
        }
        $overriddenParameters = [];
        foreach ($classMethod->params as $position => $param) {
            // by-ref writes back to the caller, variadic and promoted params are used by design
            if ($param->byRef) {
                continue;
            }
            if ($param->variadic) {
                continue;
            }
            if ($param->isPromoted()) {
                continue;
            }
            if (!$param->var instanceof Variable) {
                continue;
            }
            $paramName = $this->nodeNameResolver->getName($param->var);
            if ($paramName === null) {
                continue;
            }
            if (!$this->isOverriddenBeforeFirstUse($stmts, $paramName)) {
                continue;
            }
            $overriddenParameters[$position] = $param;
        }
        return $overriddenParameters;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function isOverriddenBeforeFirstUse(array $stmts, string $paramName): bool
    {
        foreach ($stmts as $stmt) {
            $paramVariables = $this->resolveVariablesByName($stmt, $paramName);
            if ($paramVariables === []) {
                continue;
            }
            // the very first mention must be a plain overriding assign, with no read on the right side
            if (count($paramVariables) !== 1) {
                return \false;
            }
            if (!$stmt instanceof Expression) {
                return \false;
            }
            if (!$stmt->expr instanceof Assign) {
                return \false;
            }
            return $stmt->expr->var === $paramVariables[0];
        }
        return \false;
    }
    /**
     * @return Variable[]
     */
    private function resolveVariablesByName(Stmt $stmt, string $paramName): array
    {
        /** @var Variable[] $variables */
        $variables = $this->betterNodeFinder->find($stmt, fn(Node $node): bool => $node instanceof Variable && $this->nodeNameResolver->isName($node, $paramName));
        return $variables;
    }
    private function hasIndirectVariableUsage(ClassMethod $classMethod): bool
    {
        $foundNode = $this->betterNodeFinder->findFirst($classMethod, function (Node $node): bool {
            // variable variable, e.g. $$name
            if ($node instanceof Variable) {
                return !is_string($node->name);
            }
            if (!$node instanceof FuncCall) {
                return \false;
            }
            return $this->nodeNameResolver->isNames($node, self::INDIRECT_VARIABLE_FUNCTION_NAMES);
        });
        return $foundNode instanceof Node;
    }
}
