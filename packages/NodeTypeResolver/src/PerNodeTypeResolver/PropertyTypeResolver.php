<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;

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
        // fake property to local PropertyFetch → PHPStan understand that

        $thisVariable = new Variable('this');
        $thisVariable->setAttribute(Attribute::CLASS_NODE, $propertyNode->getAttribute(Attribute::CLASS_NODE));
        $thisVariable->setAttribute(Attribute::SCOPE, $propertyNode->getAttribute(Attribute::SCOPE));

        $propertyFetchNode = new PropertyFetch($thisVariable, (string) $propertyNode->props[0]->name);
        $propertyFetchNode->setAttribute(Attribute::SCOPE, $propertyNode->getAttribute(Attribute::SCOPE));

        return $this->nodeTypeResolver->resolve($propertyFetchNode);
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
}
