<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use Rector\Reflection\ReflectionResolver;
final class VariadicAnalyzer
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
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $call
     */
    public function hasVariadicParameters($call) : bool
    {
        $functionLikeReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($call);
        if ($functionLikeReflection === null) {
            return \false;
        }
        return $this->hasVariadicVariant($functionLikeReflection);
    }
    /**
     * @param \PHPStan\Reflection\MethodReflection|\PHPStan\Reflection\FunctionReflection $functionLikeReflection
     */
    private function hasVariadicVariant($functionLikeReflection) : bool
    {
        foreach ($functionLikeReflection->getVariants() as $variant) {
            // can be any number of arguments → nothing to limit here
            if ($variant->isVariadic()) {
                return \true;
            }
        }
        return \false;
    }
}
