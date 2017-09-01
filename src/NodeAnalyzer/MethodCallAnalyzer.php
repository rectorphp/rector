<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;

final class MethodCallAnalyzer
{
    public function isStaticMethodCallTypeAndMethod(Node $node, string $type, string $method): bool
    {
        if (! $this->isStaticMethodCallType($node, $type)) {
            return false;
        }

        /** @var StaticCall $node */
        $methodName = (string) $node->name;
        if ($methodName !== $method) {
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
}
