<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class MethodCallNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var Expr|Name
     */
    private $callerNode;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

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

        $callerNode = $node->var;
        if ($callerNode instanceof MethodCall) {
            while ($callerNode instanceof MethodCall) {
                $callerNode = $callerNode->var;
            }
        }

        if ($callerNode instanceof StaticCall) {
            while ($callerNode instanceof StaticCall) {
                $callerNode = $callerNode->class;
            }
        }

        $this->callerNode = $callerNode;
        $currentCallerName = $this->nodeNameResolver->getName($this->callerNode);

        $node->setAttribute(AttributeKey::METHOD_CALL_NODE_CALLER_NAME, $currentCallerName);
    }
}
