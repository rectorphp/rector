<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer\ReturnFilter;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Reflection\FunctionReflection;
use Rector\Core\Reflection\ReflectionResolver;
final class ExclusiveNativeFuncCallReturnMatcher
{
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @param Return_[] $returns
     * @return FuncCall[]|null
     */
    public function match(array $returns)
    {
        $funcCalls = [];
        foreach ($returns as $return) {
            // we need exact expr return
            if (!$return->expr instanceof Expr) {
                return null;
            }
            if (!$return->expr instanceof FuncCall) {
                return null;
            }
            $functionReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($return->expr);
            if (!$functionReflection instanceof FunctionReflection) {
                return null;
            }
            if (!$functionReflection->isBuiltin()) {
                return null;
            }
            $funcCalls[] = $return->expr;
        }
        return $funcCalls;
    }
}
