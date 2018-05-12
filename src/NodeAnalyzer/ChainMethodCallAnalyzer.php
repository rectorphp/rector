<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * Read-only utils for chain of MethodCall Node:
 * "$this->methodCall()->chainedMethodCall()"
 */
final class ChainMethodCallAnalyzer
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * Checks "$this->someMethod()->anotherMethod()"
     *
     * @param string[] $methods
     */
    public function isTypeAndChainCalls(Node $node, string $type, array $methods): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        // node chaining is in reverse order than code
        $methods = array_reverse($methods);

        foreach ($methods as $method) {
            if ((string) $node->name !== $method) {
                return false;
            }

            $node = $node->var;
            if ($node instanceof MethodCall) {
                continue;
            }
        }

        $variableTypes = $this->nodeTypeResolver->resolve($node);

        return in_array($type, $variableTypes, true);
    }
}
