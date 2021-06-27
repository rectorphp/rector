<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Php\PhpFunctionReflection;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeNameResolver\NodeNameResolver;
final class VariadicAnalyzer
{
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\AstResolver $astResolver, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
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
        if ($this->hasVariadicVariant($functionLikeReflection)) {
            return \true;
        }
        if ($functionLikeReflection instanceof \PHPStan\Reflection\Php\PhpFunctionReflection) {
            $function = $this->astResolver->resolveFunctionFromFunctionReflection($functionLikeReflection);
            if (!$function instanceof \PhpParser\Node\Stmt\Function_) {
                return \false;
            }
            return (bool) $this->betterNodeFinder->findFirst($function->stmts, function (\PhpParser\Node $node) : bool {
                if (!$node instanceof \PhpParser\Node\Expr\FuncCall) {
                    return \false;
                }
                return $this->nodeNameResolver->isNames($node, ['func_get_args', 'func_num_args', 'func_get_arg']);
            });
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
