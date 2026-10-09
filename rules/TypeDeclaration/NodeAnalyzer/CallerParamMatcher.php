<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\AstResolver;
final class CallerParamMatcher
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, AstResolver $astResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
    }
    public function matchParentParam(StaticCall $parentStaticCall, Param $param, Scope $scope): ?Param
    {
        $methodName = $this->nodeNameResolver->getName($parentStaticCall->name);
        if ($methodName === null) {
            return null;
        }
        // match current param to parent call position
        $parentStaticCallArgPosition = $this->matchCallArgPosition($parentStaticCall, $param);
        if ($parentStaticCallArgPosition === null) {
            return null;
        }
        return $this->resolveParentMethodParam($scope, $methodName, $parentStaticCallArgPosition);
    }
    private function matchCallArgPosition(StaticCall $call, Param $param): ?int
    {
        $paramName = $this->nodeNameResolver->getName($param);
        foreach ($call->args as $argPosition => $arg) {
            if (!$arg instanceof Arg) {
                continue;
            }
            if (!$arg->value instanceof Variable) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($arg->value, $paramName)) {
                continue;
            }
            return $argPosition;
        }
        return null;
    }
    private function resolveParentMethodParam(Scope $scope, string $methodName, int $paramPosition): ?Param
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if (!$parentClassReflection->hasMethod($methodName)) {
                continue;
            }
            $parentClassMethod = $this->astResolver->resolveClassMethod($parentClassReflection->getName(), $methodName);
            if (!$parentClassMethod instanceof ClassMethod) {
                continue;
            }
            return $parentClassMethod->params[$paramPosition] ?? null;
        }
        return null;
    }
}
