<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class CallerTypeNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof MethodCall) {
            $types = $this->nodeTypeResolver->resolve($node->var);
            $node->setAttribute(Attribute::CALLER_TYPES, $types);

            return $node;
        }

        return null;
    }
}
