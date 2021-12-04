<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeNameResolver\NodeNameResolver;

final class VariadicAnalyzer
{
    public function __construct(
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly AstResolver $astResolver,
        private readonly ReflectionResolver $reflectionResolver
    ) {
    }

    public function hasVariadicParameters(FuncCall | StaticCall | MethodCall $call): bool
    {
        $functionLikeReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($call);
        if ($functionLikeReflection === null) {
            return false;
        }

        if ($this->hasVariadicVariant($functionLikeReflection)) {
            return true;
        }

        if ($functionLikeReflection instanceof FunctionReflection) {
            $function = $this->astResolver->resolveFunctionFromFunctionReflection($functionLikeReflection);
            if (! $function instanceof Function_) {
                return false;
            }

            return (bool) $this->betterNodeFinder->findFirst($function->stmts, function (Node $node): bool {
                if (! $node instanceof FuncCall) {
                    return false;
                }

                return $this->nodeNameResolver->isNames($node, ['func_get_args', 'func_num_args', 'func_get_arg']);
            });
        }

        return false;
    }

    private function hasVariadicVariant(MethodReflection | FunctionReflection $functionLikeReflection): bool
    {
        foreach ($functionLikeReflection->getVariants() as $parametersAcceptor) {
            // can be any number of arguments → nothing to limit here
            if ($parametersAcceptor->isVariadic()) {
                return true;
            }
        }

        return false;
    }
}
