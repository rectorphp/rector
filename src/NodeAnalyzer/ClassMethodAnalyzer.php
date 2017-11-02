<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Node\Attribute;

/**
 * Checks "public function methodCall()"
 */
final class ClassMethodAnalyzer
{
    /**
     * @param string[] $methods
     */
    public function isTypeAndMethods(Node $node, string $type, array $methods): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        /** @var MethodCall $node */
        return in_array($node->name->toString(), $methods, true);
    }

    private function isType(Node $node, string $type): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        $nodeTypes = (array) $node->getAttributes(Attribute::TYPES);

        return in_array($type, $nodeTypes, true);
    }
}
