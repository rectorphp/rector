<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class MethodCallNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var Node\Expr
     */
    private $currentCaller;

    /**
     * @return int|Node|void|null
     */
    public function enterNode(Node $node)
    {
        $this->processMethodCall($node);

        return $node;
    }

    private function processMethodCall(Node $node): void
    {
        if (! $node instanceof MethodCall) {
            return;
        }

        if (! $node->var instanceof MethodCall) {
            $this->currentCaller = $node->var;
        }

        $node->setAttribute(AttributeKey::METHOD_CALL_NODE_VARIABLE, $this->currentCaller);


    }
}
