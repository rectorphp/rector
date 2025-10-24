<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\NullType;
use Rector\Reflection\ReflectionResolver;
final class CallLikeParamDefaultResolver
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
     * @return int[]
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_ $callLike
     */
    public function resolveNullPositions($callLike): array
    {
        $methodReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($callLike);
        if (!$methodReflection instanceof MethodReflection) {
            return [];
        }
        $nullPositions = [];
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($methodReflection->getVariants());
        foreach ($extendedParametersAcceptor->getParameters() as $position => $extendedParameterReflection) {
            if (!$extendedParameterReflection->getDefaultValue() instanceof NullType) {
                continue;
            }
            $nullPositions[] = $position;
        }
        return $nullPositions;
    }
}
