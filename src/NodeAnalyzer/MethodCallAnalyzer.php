<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
//use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
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

//    /**
//     * Checks "SomeClassOfSpecificType::specificMethodName()"
//     */
//    public function isStaticMethodCallTypeAndMethod(Node $node, string $type, string $method): bool
//    {
//        if (! $this->isStaticMethodCallType($node, $type)) {
//            return false;
//        }
//
//        /** @var StaticCall $node */
//        return (string) $node->name === $method;
//    }

//    /**
//     * Checks "SomeClassOfSpecificType::specificMethodName()"
//     *
//     * @param string[] $methodNames
//     */
//    public function isStaticMethodCallTypeAndMethods(Node $node, string $type, array $methodNames): bool
//    {
//        if (! $this->isStaticMethodCallType($node, $type)) {
//            return false;
//        }
//
//        /** @var StaticCall $node */
//        $currentMethodName = (string) $node->name;
//
//        foreach ($methodNames as $methodName) {
//            if ($currentMethodName === $methodName) {
//                return true;
//            }
//        }
//
//        return false;
//    }

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

//    /**
//     * @param string[] $types
//     */
//    public function matchStaticMethodCallTypes(Node $node, array $types): ?string
//    {
//        if (! $node instanceof StaticCall) {
//            return null;
//        }
//
//        if (! $node->name instanceof Identifier) {
//            return null;
//        }
//
//        if (! $node->class instanceof Name) {
//            return null;
//        }
//
//        $nodeType = $node->class->toString();
//
//        return in_array($nodeType, $types, true) ? $nodeType : null;
//    }

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

//    /**
//     * Checks "SomeClassOfSpecificType::someMethod()"
//     */
//    private function isStaticMethodCallType(Node $node, string $type): bool
//    {
//        if (! $node instanceof StaticCall) {
//            return false;
//        }
//
//        $currentType = null;
//        if ($node->class instanceof Name) {
//            $currentType = $node->class->toString();
//        } elseif ($node->class instanceof Variable) {
//            $currentType = $node->class->getAttribute(Attribute::CLASS_NAME);
//        }
//
//        if ($currentType !== $type) {
//            return false;
//        }
//
//        return true;
//    }

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
