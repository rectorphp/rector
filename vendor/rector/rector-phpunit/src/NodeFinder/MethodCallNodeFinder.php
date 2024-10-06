<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
final class MethodCallNodeFinder
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function findByName(Expression $expression, string $methodName) : ?MethodCall
    {
        if (!$expression->expr instanceof MethodCall) {
            return null;
        }
        /** @var MethodCall|null $methodCall */
        $methodCall = $this->betterNodeFinder->findFirst($expression->expr, function (Node $node) use($methodName) : bool {
            if (!$node instanceof MethodCall) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node->name, $methodName);
        });
        return $methodCall;
    }
}
