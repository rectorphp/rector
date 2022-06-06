<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\NodeVisitor;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\New_;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\FunctionLike;
use RectorPrefix20220606\PhpParser\NodeVisitorAbstract;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
final class FunctionLikeParamArgPositionNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @return Node
     */
    public function enterNode(Node $node) : ?Node
    {
        if ($node instanceof FunctionLike) {
            foreach ($node->getParams() as $position => $param) {
                $param->setAttribute(AttributeKey::PARAMETER_POSITION, $position);
            }
        }
        if ($node instanceof MethodCall || $node instanceof StaticCall || $node instanceof FuncCall || $node instanceof New_) {
            foreach ($node->args as $position => $arg) {
                $arg->setAttribute(AttributeKey::ARGUMENT_POSITION, $position);
            }
        }
        return $node;
    }
}
