<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Param;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\NodeTraverser\SimpleNodeTraverser;
final class ParamDefaultNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
{
    public function enterNode(Node $node): ?Node
    {
        if (!$node instanceof Param) {
            return null;
        }
        if (!$node->default instanceof Expr) {
            return null;
        }
        SimpleNodeTraverser::decorateWithAttributeValue($node->default, AttributeKey::IS_PARAM_DEFAULT, \true);
        return null;
    }
}
