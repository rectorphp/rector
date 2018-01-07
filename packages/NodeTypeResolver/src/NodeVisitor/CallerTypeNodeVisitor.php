<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\NodeCallerTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * This will tell the type of Node, which is calling this method
 *
 * E.g.:
 * - {$this}->callMe()
 * - $this->{getThis()}->callMe()
 * - {new John}->callMe()
 */
final class CallerTypeNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var NodeCallerTypeResolver
     */
    private $nodeCallerTypeResolver;
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeCallerTypeResolver $nodeCallerTypeResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeCallerTypeResolver = $nodeCallerTypeResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof MethodCall) {
            $nodeTypeResolverTypes = $this->nodeTypeResolver->resolve($node->var);
            $types = $this->nodeCallerTypeResolver->resolve($node);

            if ($nodeTypeResolverTypes !== $types) {
                dump($nodeTypeResolverTypes);
                dump($types);
//                throw new \Exception('aa');
//                die;
            }

            $node->setAttribute(Attribute::CALLER_TYPES, $types);

            return $node;
        }

        return null;
    }
}
