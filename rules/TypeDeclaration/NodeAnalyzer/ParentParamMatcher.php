<?php

declare(strict_types=1);

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
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private AstResolver $astResolver
    ) {
    }

    public function matchParentParam(StaticCall $parentStaticCall, Param $param, Scope $scope): ?Param
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

    private function matchParentStaticCallArgPosition(StaticCall $parentStaticCall, Param $param): int | null
    {
        $paramName = $this->nodeNameResolver->getName($param);

        foreach ($parentStaticCall->args as $argPosition => $arg) {
            if (! $arg->value instanceof Variable) {
                continue;
            }

            if (! $this->nodeNameResolver->isName($arg->value, $paramName)) {
                continue;
            }

            return $argPosition;
        }

        return null;
    }

    private function resolveParentMethodParam(Scope $scope, string $methodName, int $paramPosition): ?Param
    {
        /** @var ClassReflection $classReflection */
        $classReflection = $scope->getClassReflection();

        foreach ($classReflection->getParents() as $parnetClassReflection) {
            if (! $parnetClassReflection->hasMethod($methodName)) {
                continue;
            }

            $parentClassMethod = $this->astResolver->resolveClassMethod($parnetClassReflection->getName(), $methodName);
            if (! $parentClassMethod instanceof ClassMethod) {
                continue;
            }

            return $parentClassMethod->params[$paramPosition] ?? null;
        }

        return null;
    }
}
