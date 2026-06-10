<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\NullType;
use Rector\NodeAnalyzer\VariadicAnalyzer;
use Rector\Reflection\ReflectionResolver;
final class CallLikeParamDefaultResolver
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private VariadicAnalyzer $variadicAnalyzer;
    public function __construct(ReflectionResolver $reflectionResolver, VariadicAnalyzer $variadicAnalyzer)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->variadicAnalyzer = $variadicAnalyzer;
    }
    /**
     * @return int[]
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\FuncCall $callLike
     */
    public function resolveNullPositions($callLike): array
    {
        $reflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($callLike);
        if (!$reflection instanceof MethodReflection && !$reflection instanceof FunctionReflection) {
            return [];
        }
        if ($reflection instanceof MethodReflection && $reflection->getName() === 'get') {
            $classReflection = $reflection->getDeclaringClass();
            if ($classReflection->getName() === 'Ds\Map') {
                return [];
            }
        }
        if ($this->variadicAnalyzer->hasVariadicParameters($callLike)) {
            return [];
        }
        $nullPositions = [];
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($reflection->getVariants());
        foreach ($extendedParametersAcceptor->getParameters() as $position => $extendedParameterReflection) {
            if (!$extendedParameterReflection->getDefaultValue() instanceof NullType) {
                continue;
            }
            $nullPositions[] = $position;
        }
        return $nullPositions;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\FuncCall $callLike
     */
    public function resolvePositionParameterByName($callLike, string $parameterName): ?int
    {
        $reflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($callLike);
        if (!$reflection instanceof MethodReflection && !$reflection instanceof FunctionReflection) {
            return null;
        }
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($reflection->getVariants());
        foreach ($extendedParametersAcceptor->getParameters() as $position => $extendedParameterReflection) {
            if ($extendedParameterReflection->getName() === $parameterName) {
                return $position;
            }
        }
        return null;
    }
}
