<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\DeadCode\NodeManipulator\VariadicFunctionLikeDetector;
final class VariadicAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeManipulator\VariadicFunctionLikeDetector
     */
    private $variadicFunctionLikeDetector;
    public function __construct(AstResolver $astResolver, ReflectionResolver $reflectionResolver, VariadicFunctionLikeDetector $variadicFunctionLikeDetector)
    {
        $this->astResolver = $astResolver;
        $this->reflectionResolver = $reflectionResolver;
        $this->variadicFunctionLikeDetector = $variadicFunctionLikeDetector;
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
        if ($this->hasVariadicVariant($functionLikeReflection)) {
            return \true;
        }
        if ($functionLikeReflection instanceof FunctionReflection) {
            $function = $this->astResolver->resolveFunctionFromFunctionReflection($functionLikeReflection);
            if (!$function instanceof Function_) {
                return \false;
            }
            return $this->variadicFunctionLikeDetector->isVariadic($function);
        }
        return \false;
    }
    /**
     * @param \PHPStan\Reflection\MethodReflection|\PHPStan\Reflection\FunctionReflection $functionLikeReflection
     */
    private function hasVariadicVariant($functionLikeReflection) : bool
    {
        foreach ($functionLikeReflection->getVariants() as $parametersAcceptor) {
            // can be any number of arguments → nothing to limit here
            if ($parametersAcceptor->isVariadic()) {
                return \true;
            }
        }
        return \false;
    }
}
