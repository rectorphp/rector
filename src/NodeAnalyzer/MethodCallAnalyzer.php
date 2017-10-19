<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Node\Attribute;

final class MethodCallAnalyzer
{
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
     * Checks "SomeClassOfSpecificType::specificMethodName()"
     */
    public function isStaticMethodCallTypeAndMethod(Node $node, string $type, string $method): bool
    {
        if (! $this->isStaticMethodCallType($node, $type)) {
            return false;
        }

        /** @var StaticCall $node */
        return (string) $node->name === $method;
    }

    /**
     * Checks "SomeClassOfSpecificType::specificMethodName()"
     *
     * @param string[] $methodNames
     */
    public function isStaticMethodCallTypeAndMethods(Node $node, string $type, array $methodNames): bool
    {
        if (! $this->isStaticMethodCallType($node, $type)) {
            return false;
        }

        /** @var StaticCall $node */
        $currentMethodName = (string) $node->name;

        foreach ($methodNames as $methodName) {
            if ($currentMethodName === $methodName) {
                return true;
            }
        }

        return false;
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

        dump($node);

        $variableType = $this->findVariableType($node);

        return $variableType === $type;
    }

    /**
     * Checks "SomeClassOfSpecificType::someMethod()"
     */
    private function isStaticMethodCallType(Node $node, string $type): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        $currentType = null;
        if ($node->class instanceof Name) {
            $currentType = $node->class->toString();
        } elseif ($node->class instanceof Variable) {
            $currentType = $node->class->getAttribute(Attribute::CLASS_NAME);
        }

        if ($currentType !== $type) {
            return false;
        }

        return true;
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
