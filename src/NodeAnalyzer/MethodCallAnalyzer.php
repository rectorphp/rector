<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;

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

        if ($node->class->toString() !== $type) {
            return false;
        }

        return true;
    }

    private function findVariableType(MethodCall $methodCallNode): string
    {
        $varNode = $methodCallNode->var;

        while ($varNode->getAttribute('type') === null) {
            $varNode = $varNode->var;
        }

        return (string) $varNode->getAttribute('type');
    }
}
