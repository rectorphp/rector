<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class FunctionLikeParamPositionNodeVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $node) : ?Node
    {
        if ($node instanceof FunctionLike) {
            foreach ($node->getParams() as $position => $param) {
                $param->setAttribute(AttributeKey::PARAMETER_POSITION, $position);
            }
        }
        return $node;
    }
}
