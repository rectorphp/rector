<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\NodeAnalyzer\CallLikeExpectsThisBoundClosureArgsAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class CallLikeThisBoundClosureArgsNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
{
    /**
     * @readonly
     */
    private CallLikeExpectsThisBoundClosureArgsAnalyzer $callLikeExpectsThisBindedClosureArgsAnalyzer;
    public function __construct(CallLikeExpectsThisBoundClosureArgsAnalyzer $callLikeExpectsThisBindedClosureArgsAnalyzer)
    {
        $this->callLikeExpectsThisBindedClosureArgsAnalyzer = $callLikeExpectsThisBindedClosureArgsAnalyzer;
    }
    public function enterNode(Node $node): ?Node
    {
        if (!$node instanceof MethodCall && !$node instanceof StaticCall && !$node instanceof FuncCall) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $this->callLikeExpectsThisBindedClosureArgsAnalyzer->getArgsUsingThisBoundClosure($node);
        if ($args === []) {
            return null;
        }
        foreach ($args as $arg) {
            if ($arg->value instanceof Closure && !$arg->hasAttribute(AttributeKey::IS_CLOSURE_USES_THIS)) {
                $arg->value->setAttribute(AttributeKey::IS_CLOSURE_USES_THIS, \true);
            }
        }
        return $node;
    }
}
