<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * @see \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver\PropertyTypeResolverTest
 */
final class PropertyTypeResolver implements PerNodeTypeResolverInterface, NodeTypeResolverAwareInterface
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $propertyNode
     * @return string[]
     */
    public function resolve(Node $propertyNode): array
    {
        // fake property to local PropertyFetch → PHPStan understands that
        $propertyFetchNode = new PropertyFetch(new Variable('this'), (string) $propertyNode->props[0]->name);
        $propertyFetchNode->setAttribute(AttributeKey::SCOPE, $propertyNode->getAttribute(AttributeKey::SCOPE));

        return $this->nodeTypeResolver->resolve($propertyFetchNode);
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
}
