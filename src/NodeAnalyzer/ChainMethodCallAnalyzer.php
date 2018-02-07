<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeTypeResolver\NodeTypeResolver;

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

        $currentMethodCall = $node;

        foreach ($methods as $method) {
            if ((string) $currentMethodCall->name !== $method) {
                return false;
            }

            $currentMethodCall = $currentMethodCall->var;
            if ($currentMethodCall instanceof MethodCall) {
                continue;
            }
        }

        $variableTypes = $this->nodeTypeResolver->resolve($currentMethodCall);

        return in_array($type, $variableTypes, true);
    }
}
