<?php

declare(strict_types=1);

namespace Rector\Naming\Matcher;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Naming\ValueObject\VariableAndCallForeach;
use Rector\NodeNameResolver\NodeNameResolver;

final class ForeachMatcher
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private CallMatcher $callMatcher,
        private BetterNodeFinder $betterNodeFinder
    ) {
    }

    public function match(Foreach_ $foreach): ?VariableAndCallForeach
    {
        $call = $this->callMatcher->matchCall($foreach);
        if ($call === null) {
            return null;
        }

        if (! $foreach->valueVar instanceof Variable) {
            return null;
        }

        $functionLike = $this->getFunctionLike($foreach);
        if ($functionLike === null) {
            return null;
        }

        $variableName = $this->nodeNameResolver->getName($foreach->valueVar);
        if ($variableName === null) {
            return null;
        }

        return new VariableAndCallForeach($foreach->valueVar, $call, $variableName, $functionLike);
    }

    /**
     * @return ClassMethod|Function_|Closure|null
     */
    private function getFunctionLike(Foreach_ $foreach): ?Node
    {
        return $this->betterNodeFinder->findParentTypes(
            $foreach,
            [Closure::class, ClassMethod::class, Function_::class]
        );
    }
}
