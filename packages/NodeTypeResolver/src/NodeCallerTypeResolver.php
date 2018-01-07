<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeCallerTypeResolver\PerNodeCallerTypeResolverInterface;
use Rector\NodeTypeResolver\PerNodeCallerTypeResolver\MethodCallCallerTypeResolver;

/**
 * This will tell the type of Node, which is calling this method
 *
 * E.g.:
 * - {$this}->callMe()
 * - $this->{getThis()}->callMe()
 * - {parent}::callMe()
 */
final class NodeCallerTypeResolver
{
    /**
     * @var MethodCallCallerTypeResolver
     */
    private $methodCallCallerTypeResolver;

    public function __construct(MethodCallCallerTypeResolver $methodCallCallerTypeResolver)
    {
        $this->methodCallCallerTypeResolver = $methodCallCallerTypeResolver;
    }

    /**
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        if ($node instanceof Node\Expr\MethodCall) {
            $callerTypes = $this->methodCallCallerTypeResolver->resolve($node);
            $callerTypes = array_unique($callerTypes);
            $node->setAttribute(Attribute::CALLER_TYPES, $callerTypes);

            return $callerTypes;
        }

        return [];
    }
}
