<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\PhpParser\AstResolver;
use Rector\NodeNameResolver\NodeNameResolver;
final class ParentParamMatcher
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
    public function matchParentParam(\PhpParser\Node\Expr\StaticCall $parentStaticCall, \PhpParser\Node\Param $param, \PHPStan\Analyser\Scope $scope) : ?\PhpParser\Node\Param
    {
        $methodName = $this->nodeNameResolver->getName($parentStaticCall->name);
        if ($methodName === null) {
            return null;
        }
        // match current param to parent call position
        $parentStaticCallArgPosition = $this->matchParentStaticCallArgPosition($parentStaticCall, $param);
        if ($parentStaticCallArgPosition === null) {
            return null;
        }
        return $this->resolveParentMethodParam($scope, $methodName, $parentStaticCallArgPosition);
    }
    /**
     * @return int|null
     */
    private function matchParentStaticCallArgPosition(\PhpParser\Node\Expr\StaticCall $parentStaticCall, \PhpParser\Node\Param $param)
    {
        $paramName = $this->nodeNameResolver->getName($param);
        foreach ($parentStaticCall->args as $argPosition => $arg) {
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
        /** @var ClassReflection $classReflection */
        $classReflection = $scope->getClassReflection();
        foreach ($classReflection->getParents() as $parnetClassReflection) {
            if (!$parnetClassReflection->hasMethod($methodName)) {
                continue;
            }
            $parentClassMethod = $this->astResolver->resolveClassMethod($parnetClassReflection->getName(), $methodName);
            if (!$parentClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
                continue;
            }
            return $parentClassMethod->params[$paramPosition] ?? null;
        }
        return null;
    }
}
