<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
final class NameNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    public function enterNode(Node $node) : ?Node
    {
        if ($node instanceof UseUse && ($node->type === Use_::TYPE_NORMAL || $node->type === Use_::TYPE_UNKNOWN)) {
            $node->name->setAttribute(AttributeKey::IS_USEUSE_NAME, \true);
            return null;
        }
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
