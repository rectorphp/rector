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
final class FunctionLikeParamArgPositionNodeVisitor extends \PhpParser\NodeVisitorAbstract
{
    /**
     * @return Node
     */
    public function enterNode(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\FunctionLike) {
            foreach ($node->getParams() as $position => $param) {
                $param->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARAMETER_POSITION, $position);
            }
        }
        if ($node instanceof \PhpParser\Node\Expr\MethodCall || $node instanceof \PhpParser\Node\Expr\StaticCall || $node instanceof \PhpParser\Node\Expr\FuncCall || $node instanceof \PhpParser\Node\Expr\New_) {
            foreach ($node->args as $position => $arg) {
                $arg->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ARGUMENT_POSITION, $position);
            }
        }
        return $node;
    }
}
