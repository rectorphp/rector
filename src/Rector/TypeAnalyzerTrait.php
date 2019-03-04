<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PHPStan\Type\Type;
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
    public function autowireTypeAnalyzerDependencies(
        NodeTypeResolver $nodeTypeResolver,
        NodeTypeAnalyzer $nodeTypeAnalyzer
    ): void {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeTypeAnalyzer = $nodeTypeAnalyzer;
    }

    protected function isType(Node $node, string $type): bool
    {
        return $this->nodeTypeResolver->isType($node, $type);
    }

    /**
     * @param string[] $types
     */
    protected function isTypes(Node $node, array $types): bool
    {
        $nodeTypes = $this->getTypes($node);
        return (bool) array_intersect($types, $nodeTypes);
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    protected function matchTypes(Node $node, array $types): array
    {
        return $this->isTypes($node, $types) ? $this->getTypes($node) : [];
    }

    protected function isStringType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isStringType($node);
    }

    protected function isStringyType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isStringyType($node);
    }

    protected function isIntegerType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isIntType($node);
    }

    protected function isFloatType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isFloatType($node);
    }

    protected function getStaticType(Node $node): ?Type
    {
        return $this->nodeTypeAnalyzer->getNodeStaticType($node);
    }

    protected function isNullableType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isNullableType($node);
    }

    protected function isNullableObjectType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isNullableObjectType($node);
    }

    protected function isNullType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isNullType($node);
    }

    protected function isBoolType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isBoolType($node);
    }

    protected function isCountableType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isCountableType($node);
    }

    protected function isArrayType(Node $node): bool
    {
        return $this->nodeTypeAnalyzer->isArrayType($node);
    }

    /**
     * @return string[]
     */
    protected function getTypes(Node $node): array
    {
        return $this->nodeTypeResolver->getTypes($node);
    }
}
