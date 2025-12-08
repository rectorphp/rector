<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class NameNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
{
    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $node->name->setAttribute(AttributeKey::IS_FUNCCALL_NAME, \true);
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
        if (!$node instanceof StaticCall) {
            return null;
        }
        if (!$node->class instanceof Name) {
            return null;
        }
        $node->class->setAttribute(AttributeKey::IS_STATICCALL_CLASS_NAME, \true);
        return null;
    }
}
