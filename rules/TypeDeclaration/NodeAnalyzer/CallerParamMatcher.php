<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\PhpParser\AstResolver;
use Rector\NodeNameResolver\NodeNameResolver;
final class CallerParamMatcher
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\AstResolver $astResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\FuncCall $call
     * @return null|\PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|\PhpParser\Node\ComplexType
     */
    public function matchCallParamType($call, \PhpParser\Node\Param $param, \PHPStan\Analyser\Scope $scope)
    {
        $callParam = $this->matchCallParam($call, $param, $scope);
        if (!$callParam instanceof \PhpParser\Node\Param) {
            return null;
        }
        return $callParam->type;
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\FuncCall $call
     */
    public function matchCallParam($call, \PhpParser\Node\Param $param, \PHPStan\Analyser\Scope $scope) : ?\PhpParser\Node\Param
    {
        $callArgPosition = $this->matchCallArgPosition($call, $param);
        if ($callArgPosition === null) {
            return null;
        }
        $classMethodOrFunction = $this->astResolver->resolveClassMethodOrFunctionFromCall($call, $scope);
        if ($classMethodOrFunction === null) {
            return null;
        }
        return $classMethodOrFunction->params[$callArgPosition] ?? null;
    }
    public function matchParentParam(\PhpParser\Node\Expr\StaticCall $parentStaticCall, \PhpParser\Node\Param $param, \PHPStan\Analyser\Scope $scope) : ?\PhpParser\Node\Param
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
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\FuncCall $call
     * @return int|null
     */
    private function matchCallArgPosition($call, \PhpParser\Node\Param $param)
    {
        $paramName = $this->nodeNameResolver->getName($param);
        foreach ($call->args as $argPosition => $arg) {
            if (!$arg instanceof \PhpParser\Node\Arg) {
                continue;
            }
            if (!$arg->value instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($arg->value, $paramName)) {
                continue;
            }
            return $argPosition;
        }
        return null;
    }
    private function resolveParentMethodParam(\PHPStan\Analyser\Scope $scope, string $methodName, int $paramPosition) : ?\PhpParser\Node\Param
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if (!$parentClassReflection->hasMethod($methodName)) {
                continue;
            }
            $parentClassMethod = $this->astResolver->resolveClassMethod($parentClassReflection->getName(), $methodName);
            if (!$parentClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
                continue;
            }
            return $parentClassMethod->params[$paramPosition] ?? null;
        }
        return null;
    }
}
