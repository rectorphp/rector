<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeAnalyzer;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait TypeAnalyzerTrait
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeTypeAnalyzer
     */
    private $nodeTypeAnalyzer;

    /**
     * @required
     */
    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @required
     */
    public function setNodeTypeAnalyzer(NodeTypeAnalyzer $nodeTypeAnalyzer): void
    {
        $this->nodeTypeAnalyzer = $nodeTypeAnalyzer;
    }

    public function isType(Node $node, string $type): bool
    {
        $nodeTypes = $this->getTypes($node);
        return in_array($type, $nodeTypes, true);
    }

    /**
     * @param string[] $types
     */
    public function isTypes(Node $node, array $types): bool
    {
        $nodeTypes = $this->getTypes($node);
        return (bool) array_intersect($types, $nodeTypes);
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    public function matchTypes(Node $node, array $types): array
    {
        return $this->isTypes($node, $types) ? $this->getTypes($node) : [];
    }

    public function isStringType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isStringType($node);
    }

    public function isNullableType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isNullableType($node);
    }

    public function isBoolType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isBoolType($node);
    }

    public function isCountableType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isCountableType($node);
    }

    /**
     * @return string[]
     */
    public function getTypes(Node $node): array
    {
        // @todo should be resolved by NodeTypeResolver internally
        if ($node instanceof ClassMethod || $node instanceof ClassConst) {
            return $this->nodeTypeResolver->resolve($node->getAttribute(Attribute::CLASS_NODE));
        }

        if ($node instanceof MethodCall || $node instanceof PropertyFetch || $node instanceof ArrayDimFetch) {
            return $this->nodeTypeResolver->resolve($node->var);
        }

        if ($node instanceof StaticCall || $node instanceof ClassConstFetch) {
            return $this->nodeTypeResolver->resolve($node->class);
        }

        if ($node instanceof Cast) {
            return $this->nodeTypeResolver->resolve($node->expr);
        }

        return $this->nodeTypeResolver->resolve($node);
    }
}
