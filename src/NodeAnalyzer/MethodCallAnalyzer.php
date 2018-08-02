<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\BetterReflection\Reflector\SmartClassReflector;
use Rector\NodeTypeResolver\NodeTypeResolver;
use ReflectionMethod;

/**
 * Read-only utils for MethodCall Node:
 * "$this->someMethod()"
 */
final class MethodCallAnalyzer
{
    /**
     * @var SmartClassReflector
     */
    private $smartClassReflector;

    /**
     * @var string[][]
     */
    private $publicMethodNamesForType = [];

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(SmartClassReflector $smartClassReflector, NodeTypeResolver $nodeTypeResolver)
    {
        $this->smartClassReflector = $smartClassReflector;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * Checks "$this->classOfSpecificType->specificMethodName()"
     *
     * @param string[] $methods
     */
    public function isTypeAndMethods(Node $node, string $type, array $methods): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        /** @var Identifier $methodCallNode */
        $methodName = $node->name;

        return in_array($methodName->toString(), $methods, true);
    }

    /**
     * Checks "$this->classOfSpecificType->specificMethodName()"
     */
    public function isTypeAndMethod(Node $node, string $type, string $method): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        /** @var Identifier $methodName */
        $methodName = $node->name;

        return $methodName->toString() === $method;
    }

    /**
     * Checks "$this->specificNameMethod()"
     */
    public function isMethod(Node $node, string $methodName): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->name instanceof Identifier) {
            return false;
        }

        return $node->name->name === $methodName;
    }

    /**
     * @param string[] $methods
     */
    public function isMethods(Node $node, array $methods): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->name instanceof Identifier) {
            return false;
        }

        return in_array($node->name->name, $methods, true);
    }

    /**
     * Checks "$this->methodCall()"
     */
    public function isType(Node $node, string $type): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        $callerNodeTypes = $this->nodeTypeResolver->resolve($node->var);

        return in_array($type, $callerNodeTypes, true);
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    public function matchTypes(Node $node, array $types): ?array
    {
        if (! $node instanceof MethodCall) {
            return null;
        }

        $nodeTypes = $this->nodeTypeResolver->resolve($node->var);

        return array_intersect($nodeTypes, $types) ? $nodeTypes : null;
    }

    /**
     * @return string[]
     */
    private function getPublicMethodNamesForType(string $type): array
    {
        if (isset($this->publicMethodNamesForType[$type])) {
            return $this->publicMethodNamesForType[$type];
        }

        $classReflection = $this->smartClassReflector->reflect($type);
        if ($classReflection === null) {
            return [];
        }

        $publicMethods = $classReflection->getMethods(ReflectionMethod::IS_PUBLIC);

        return $this->publicMethodNamesForType[$type] = array_keys($publicMethods);
    }
}
