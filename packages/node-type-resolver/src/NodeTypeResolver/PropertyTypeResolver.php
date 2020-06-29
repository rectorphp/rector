<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * @see \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver\PropertyTypeResolverTest
 */
final class PropertyTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @required
     */
    public function autowirePropertyTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $propertyNode
     */
    public function resolve(Node $propertyNode): Type
    {
        // fake property to local PropertyFetch → PHPStan understands that
        $propertyFetch = new PropertyFetch(new Variable('this'), (string) $propertyNode->props[0]->name);
        $propertyFetch->setAttribute(AttributeKey::SCOPE, $propertyNode->getAttribute(AttributeKey::SCOPE));

        return $this->nodeTypeResolver->resolve($propertyFetch);
    }
}
