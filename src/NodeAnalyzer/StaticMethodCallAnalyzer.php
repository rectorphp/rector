<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\NodeTypeResolver\NodeCallerTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class StaticMethodCallAnalyzer
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeCallerTypeResolver
     */
    private $nodeCallerTypeResolver;

    public function __construct(
        NodeTypeResolver $nodeTypeResolver,
        NodeCallerTypeResolver $nodeCallerTypeResolver
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeCallerTypeResolver = $nodeCallerTypeResolver;
    }

    /**
     * Checks "SpecificType::specificMethod()"
     */
    public function isTypeAndMethod(Node $node, string $type, string $method): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        return (string) $node->name === $method;
    }

    /**
     * Checks "SpecificType::oneOfSpecificMethods()"
     *
     * @param string[] $methodNames
     */
    public function isTypeAndMethods(Node $node, string $type, array $methodNames): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        $currentMethodName = (string) $node->name;

        foreach ($methodNames as $methodName) {
            if ($currentMethodName === $methodName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    public function matchTypes(Node $node, array $types): ?array
    {
        if (! $node instanceof StaticCall) {
            return null;
        }

        if (! $node->name instanceof Identifier) {
            return null;
        }

        if (! $node->class instanceof Name) {
            return null;
        }

        $nodeTypes = $this->nodeTypeResolver->resolve($node->class);

        return array_intersect($nodeTypes, $types) ? $nodeTypes : null;
    }

    /**
     * Checks "SpecificType::anyMethod()"
     */
    private function isType(Node $node, string $type): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        $callerTypes = $this->nodeCallerTypeResolver->resolve($node);
        return in_array($type, $callerTypes, true);
    }
}
