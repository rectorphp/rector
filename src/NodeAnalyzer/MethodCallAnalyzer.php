<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\BetterReflection\Reflector\SmartClassReflector;
use Rector\Node\Attribute;
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

    public function __construct(SmartClassReflector $smartClassReflector)
    {
        $this->smartClassReflector = $smartClassReflector;
    }

    /**
     * Checks "$this->classOfSpecificType->specificMethodName()"
     *
     * @param string[] $methods
     */
    public function isMethodCallTypeAndMethods(Node $node, string $type, array $methods): bool
    {
        if (! $this->isMethodCallType($node, $type)) {
            return false;
        }

        /** @var MethodCall $node */
        return in_array((string) $node->name, $methods, true);
    }

    /**
     * Checks "$this->classOfSpecificType->specificMethodName()"
     */
    public function isMethodCallTypeAndMethod(Node $node, string $type, string $method): bool
    {
        if (! $this->isMethodCallType($node, $type)) {
            return false;
        }

        /** @var MethodCall $node */
        return (string) $node->name === $method;
    }

    /**
     * Checks "$this->specificNameMethod()"
     */
    public function isMethodCallMethod(Node $node, string $methodName): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->name instanceof Identifier) {
            return false;
        }

        $nodeMethodName = $node->name->name;

        return $nodeMethodName === $methodName;
    }

    /**
     * Checks "$this->methodCall()"
     */
    public function isMethodCallType(Node $node, string $type): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        $variableType = $this->findVariableType($node);

        return $variableType === $type;
    }

    /**
     * @param string[] $types
     */
    public function matchMethodCallTypes(Node $node, array $types): ?string
    {
        if (! $node instanceof MethodCall) {
            return null;
        }

        $nodeType = $node->var->getAttribute(Attribute::TYPE);

        return in_array($nodeType, $types, true) ? $nodeType : null;
    }

    public function isMagicMethodCallOnType(Node $node, string $type): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        $variableType = $this->findVariableType($node);
        if ($variableType !== $type) {
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
        $publicMethods = $classReflection->getMethods(ReflectionMethod::IS_PUBLIC);

        return $this->publicMethodNamesForType[$type] = array_keys($publicMethods);
    }

    private function findVariableType(MethodCall $methodCallNode): string
    {
        $varNode = $methodCallNode->var;

        // itterate up, @todo: handle in TypeResover
        while ($varNode->getAttribute(Attribute::TYPE) === null) {
            if (property_exists($varNode, 'var')) {
                $varNode = $varNode->var;
            } else {
                break;
            }
        }

        return (string) $varNode->getAttribute(Attribute::TYPE);
    }
}
