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
     * @param string[] $methodsNames
     */
    public function isMethodCallTypeAndMethods(Node $node, string $type, array $methodsNames): bool
    {
        if (! $this->isMethodCallType($node, $type)) {
            return false;
        }

        return in_array((string) $node->name, $methodsNames, true);
    }

    /**
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

    private function isMethodCallType(Node $node, string $type): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        $variableType = $this->findVariableType($node);
        if ($variableType !== $type) {
            return false;
        }

        return true;
    }

    private function isStaticMethodCallType(Node $node, string $type): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

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
