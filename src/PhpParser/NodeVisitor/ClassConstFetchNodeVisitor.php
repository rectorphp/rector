<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ClassConstFetchNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
{
    public function enterNode(Node $node): ?Node
    {
        if (!$node instanceof ClassConstFetch) {
            return null;
        }
        // pass value metadata to class node
        if (!$node->name instanceof Identifier) {
            return null;
        }
        $node->class->setAttribute(AttributeKey::CLASS_CONST_FETCH_NAME, $node->name->toString());
        return null;
    }
}
