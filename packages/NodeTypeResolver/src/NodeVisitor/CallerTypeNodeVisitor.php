<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PerNodeCallerTypeResolver\MethodCallCallerTypeResolver;

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
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var MethodCallCallerTypeResolver
     */
    private $methodCallCallerTypeResolver;

    public function __construct(
        NodeTypeResolver $nodeTypeResolver,
        MethodCallCallerTypeResolver $methodCallCallerTypeResolver
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->methodCallCallerTypeResolver = $methodCallCallerTypeResolver;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof MethodCall) {
            $nodeTypeResolverTypes = $this->nodeTypeResolver->resolve($node->var);
            $types = $this->methodCallCallerTypeResolver->resolve($node);

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
