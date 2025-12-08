<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ArgNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
{
    public function enterNode(Node $node): ?Node
    {
        if (!$node instanceof FuncCall) {
            return null;
        }
        if (!$node->name instanceof Name) {
            return null;
        }
        // has no args
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $funcCallName = $node->name->toString();
        foreach ($node->getArgs() as $arg) {
            $arg->value->setAttribute(AttributeKey::FROM_FUNC_CALL_NAME, $funcCallName);
        }
        return null;
    }
}
