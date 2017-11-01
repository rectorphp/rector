<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\NodeVisitorAbstract;

final class ReturnTypeResolver extends NodeVisitorAbstract
{
    public function enterNode(Node $node)
    {
        if (! $node instanceof MethodCall && ! $node instanceof StaticCall) {
            return $node;
        }

        dump($node);
        die;
    }
}
