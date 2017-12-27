<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * Checks "public function methodCall()"
 */
final class ClassMethodAnalyzer
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @param string[] $methods
     */
    public function isTypeAndMethods(Node $node, string $type, array $methods): bool
    {
        if (! $this->isType($node, $type)) {
            return false;
        }

        return in_array($node->name->toString(), $methods, true);
    }

    private function isType(Node $node, string $type): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        $nodeTypes = $this->nodeTypeResolver->resolve($classNode);

        return in_array($type, $nodeTypes, true);
    }
}
