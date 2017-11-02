<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\NodeCallerTypeResolver;

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

    public function __construct(NodeCallerTypeResolver $nodeCallerTypeResolver)
    {
        $this->nodeCallerTypeResolver = $nodeCallerTypeResolver;
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof StaticCall && ! $node instanceof MethodCall) {
            return $node;
        }

        $types = $this->nodeCallerTypeResolver->resolve($node);
        if ($types) {
            $node->setAttribute(Attribute::CALLER_TYPES, $types);
        }

        return null;
    }
}
