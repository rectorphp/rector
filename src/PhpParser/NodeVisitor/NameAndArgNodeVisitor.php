<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * Marks call/name context attributes on the name or class node, plus the func-call name on its
 * arg values, so later rules can tell how a name is used without re-resolving the parent.
 */
final class NameAndArgNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
{
    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $node->name->setAttribute(AttributeKey::IS_FUNCCALL_NAME, \true);
            if (!$node->isFirstClassCallable()) {
                $funcCallName = $node->name->toString();
                foreach ($node->getArgs() as $arg) {
                    $arg->value->setAttribute(AttributeKey::FROM_FUNC_CALL_NAME, $funcCallName);
                }
            }
            return null;
        }
        if ($node instanceof ConstFetch) {
            $node->name->setAttribute(AttributeKey::IS_CONSTFETCH_NAME, \true);
            return null;
        }
        if ($node instanceof New_ && $node->class instanceof Name) {
            $node->class->setAttribute(AttributeKey::IS_NEW_INSTANCE_NAME, \true);
            return null;
        }
        if ($node instanceof StaticCall && $node->class instanceof Name) {
            $node->class->setAttribute(AttributeKey::IS_STATICCALL_CLASS_NAME, \true);
            return null;
        }
        // pass value metadata to class node
        if ($node instanceof ClassConstFetch && $node->name instanceof Identifier) {
            $node->class->setAttribute(AttributeKey::CLASS_CONST_FETCH_NAME, $node->name->toString());
        }
        return null;
    }
}
