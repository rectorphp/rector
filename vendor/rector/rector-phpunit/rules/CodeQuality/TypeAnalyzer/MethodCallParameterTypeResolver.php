<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\TypeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Type;
use Rector\Reflection\ReflectionResolver;
final class MethodCallParameterTypeResolver
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
     * @return Type[]|null
     */
    public function resolve(MethodCall $methodCall): ?array
    {
        if ($methodCall->getArgs() === []) {
            return null;
        }
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromMethodCall($methodCall);
        if (!$methodReflection instanceof MethodReflection) {
            return null;
        }
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($methodReflection->getVariants());
        $parameterTypesByPosition = [];
        foreach ($extendedParametersAcceptor->getParameters() as $extendedParameterReflection) {
            $parameterTypesByPosition[] = $extendedParameterReflection->getType();
        }
        return $parameterTypesByPosition;
    }
}
