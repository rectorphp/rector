<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Closure;
use PHPStan\Reflection\ExtendedParameterReflection;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Rector\Reflection\ReflectionResolver;
final class CallLikeExpectsThisBoundClosureArgsAnalyzer
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @return Arg[]
     */
    public function getArgsUsingThisBoundClosure(CallLike $callLike): array
    {
        if ($callLike->isFirstClassCallable() || $callLike->getArgs() === []) {
            return [];
        }
        $callArgs = $callLike->getArgs();
        $hasClosureArg = (bool) array_filter($callArgs, fn(Arg $arg): bool => $arg->value instanceof Closure);
        if (!$hasClosureArg) {
            return [];
        }
        $argsUsingThisBoundClosure = [];
        $reflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($callLike);
        if ($reflection === null) {
            return [];
        }
        $scope = $callLike->getAttribute(AttributeKey::SCOPE);
        if ($scope === null) {
            return [];
        }
        $parametersAcceptor = ParametersAcceptorSelectorVariantsWrapper::select($reflection, $callLike, $scope);
        $parameters = $parametersAcceptor->getParameters();
        foreach ($callArgs as $index => $arg) {
            if (!$arg->value instanceof Closure) {
                continue;
            }
            if ((($nullsafeVariable1 = $arg->name) ? $nullsafeVariable1->name : null) !== null) {
                foreach ($parameters as $parameter) {
                    if (!$parameter instanceof ExtendedParameterReflection) {
                        continue;
                    }
                    $hasObjectBinding = (bool) $parameter->getClosureThisType();
                    if ($hasObjectBinding && $arg->name->name === $parameter->getName()) {
                        $argsUsingThisBoundClosure[] = $arg;
                    }
                }
                continue;
            }
            if (!is_string(($nullsafeVariable2 = $arg->name) ? $nullsafeVariable2->name : null)) {
                $parameter = $parameters[$index] ?? null;
                if (!$parameter instanceof ExtendedParameterReflection) {
                    continue;
                }
                $hasObjectBinding = (bool) $parameter->getClosureThisType();
                if ($hasObjectBinding) {
                    $argsUsingThisBoundClosure[] = $arg;
                }
            }
        }
        return $argsUsingThisBoundClosure;
    }
}
