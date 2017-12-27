<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\BetterReflection\Reflector\SmartClassReflector;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\NodeCallerTypeResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use ReflectionMethod;

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

    /**
     * @var NodeCallerTypeResolver
     */
    private $nodeCallerTypeResolver;

    public function __construct(
        SmartClassReflector $smartClassReflector,
        NodeTypeResolver $nodeTypeResolver,
        NodeCallerTypeResolver $nodeCallerTypeResolver
    ) {
        $this->smartClassReflector = $smartClassReflector;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeCallerTypeResolver = $nodeCallerTypeResolver;
    }

    /**
     * @param string[] $types
     * @param string[] $methods
     */
    public function isTypesAndMethods(Node $node, array $types, array $methods): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        $callerNodeTypes = $this->nodeCallerTypeResolver->resolve($node);
        if (! array_intersect($types, $callerNodeTypes)) {
            return false;
        }

        return $this->isMethods($node, $methods);
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

        return in_array($node->name->toString(), $methods, true);
    }

    /**
     * Checks "$this->classOfSpecificType->specificMethodName()"
     */
    public function isTypeAndMethod(Node $node, string $type, string $method): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        return $node->name->toString() === $method;
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

        $callerNodeTypes = (array) $node->getAttribute(Attribute::CALLER_TYPES);

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

    public function isTypeAndMagic(Node $node, string $type): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        $nodeMethodName = $node->name->name;

        $publicMethodNames = $this->getPublicMethodNamesForType($type);

        return ! in_array($nodeMethodName, $publicMethodNames, true);
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
