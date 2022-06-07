<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\FunctionLike;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
