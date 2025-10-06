<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\NodeFinder;

use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
final class ArrayMapClosureExprFinder
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return array<Closure|ArrowFunction>
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function findByVariableName($functionLike, string $variableName): array
    {
        if ($functionLike->stmts === null) {
            return [];
        }
        /** @var FuncCall[] $funcCalls */
        $funcCalls = $this->betterNodeFinder->findInstancesOfScoped($functionLike->stmts, FuncCall::class);
        $arrayMapClosures = [];
        foreach ($funcCalls as $funcCall) {
            if ($funcCall->isFirstClassCallable()) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($funcCall, 'array_map')) {
                continue;
            }
            $secondArg = $funcCall->getArgs()[1];
            if (!$secondArg->value instanceof Variable) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($secondArg->value, $variableName)) {
                continue;
            }
            $firstArg = $funcCall->getArgs()[0];
            if (!$firstArg->value instanceof Closure && !$firstArg->value instanceof ArrowFunction) {
                continue;
            }
            $arrayMapClosures[] = $firstArg->value;
        }
        return $arrayMapClosures;
    }
}
