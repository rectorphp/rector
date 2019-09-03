<?php declare(strict_types=1);

namespace Rector\Rector\AbstractRector;

use PhpParser\Node;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait NodeTypeResolverTrait
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @required
     */
    public function autowireTypeAnalyzerDependencies(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
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

    protected function isStringyType(Node $node): bool
    {
        return $this->nodeTypeResolver->isStringyType($node);
    }

    protected function isNumberType(Node $node): bool
    {
        return $this->nodeTypeResolver->isNumberType($node);
    }

    protected function isStaticType(Node $node, string $staticTypeClass): bool
    {
        return $this->nodeTypeResolver->isStaticType($node, $staticTypeClass);
    }

    protected function getStaticType(Node $node): ?Type
    {
        return $this->nodeTypeResolver->getStaticType($node);
    }

    protected function isNullableType(Node $node): bool
    {
        return $this->nodeTypeResolver->isNullableType($node);
    }

    protected function isNullableObjectType(Node $node): bool
    {
        return $this->nodeTypeResolver->isNullableObjectType($node);
    }

    protected function isCountableType(Node $node): bool
    {
        return $this->nodeTypeResolver->isCountableType($node);
    }

    protected function isArrayType(Node $node): bool
    {
        return $this->nodeTypeResolver->isArrayType($node);
    }

    /**
     * @return string[]
     */
    protected function getTypes(Node $node): array
    {
        return $this->nodeTypeResolver->getTypes($node);
    }
}
