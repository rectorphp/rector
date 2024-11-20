<?php

declare (strict_types=1);
namespace Rector\Naming\Matcher;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Naming\ValueObject\VariableAndCallAssign;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
final class VariableAndCallAssignMatcher
{
    /**
     * @readonly
     */
    private \Rector\Naming\Matcher\CallMatcher $callMatcher;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(\Rector\Naming\Matcher\CallMatcher $callMatcher, NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->callMatcher = $callMatcher;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function match(Assign $assign, $functionLike) : ?VariableAndCallAssign
    {
        $call = $this->callMatcher->matchCall($assign);
        if (!$call instanceof Node) {
            return null;
        }
        if (!$assign->var instanceof Variable) {
            return null;
        }
        $variableName = $this->nodeNameResolver->getName($assign->var);
        if ($variableName === null) {
            return null;
        }
        $isVariableFoundInCallArgs = (bool) $this->betterNodeFinder->findFirst($call->isFirstClassCallable() ? [] : $call->getArgs(), fn(Node $subNode): bool => $subNode instanceof Variable && $this->nodeNameResolver->isName($subNode, $variableName));
        if ($isVariableFoundInCallArgs) {
            return null;
        }
        return new VariableAndCallAssign($assign->var, $call, $assign, $variableName, $functionLike);
    }
}
