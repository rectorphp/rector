<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\BetterReflection\Reflector\PropertyReflector;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\TypeContext;

final class PropertyFetchTypeResolver implements PerNodeTypeResolverInterface, NodeTypeResolverAwareInterface
{
    /**
     * @var TypeContext
     */
    private $typeContext;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var PropertyReflector
     */
    private $propertyReflector;

    public function __construct(TypeContext $typeContext, PropertyReflector $propertyReflector)
    {
        $this->typeContext = $typeContext;
        $this->propertyReflector = $propertyReflector;
    }

    public function getNodeClass(): string
    {
        return PropertyFetch::class;
    }

    /**
     * @param PropertyFetch $propertyFetchNode
     * @return string[]
     */
    public function resolve(Node $propertyFetchNode): array
    {
        $propertyName = $propertyFetchNode->name->toString();

        // e.g. $r->getParameters()[0]->name
        if ($propertyFetchNode->var instanceof ArrayDimFetch) {
            $types = $this->nodeTypeResolver->resolve($propertyFetchNode->var->var);
            $type = array_shift($types);

            $propertyType = $this->propertyReflector->getPropertyType($type, $propertyName);
            return [$propertyType];

            // @todo: keep for now for possible BC changes of other resolvers
            // return $this->nodeTypeResolver->resolve($propertyFetchNode->var);
        }

        if ($propertyFetchNode->var instanceof New_) {
            return $this->nodeTypeResolver->resolve($propertyFetchNode->var);
        }

        // e.g. $this->property
        if ($propertyFetchNode->var->name === 'this') {
            $propertyName = $this->resolvePropertyName($propertyFetchNode);

            return $this->typeContext->getTypesForProperty($propertyName);
        }

        // e.g. $this->property->anotherProperty
        $types = $this->nodeTypeResolver->resolve($propertyFetchNode->var);
        $type = array_shift($types);

        $type = $this->propertyReflector->getPropertyType($type, $propertyName);
        if ($type) {
            return [$type];
        }

        return [];
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    private function resolvePropertyName(PropertyFetch $propertyFetchNode): string
    {
        if ($propertyFetchNode->name instanceof Variable) {
            return $propertyFetchNode->name->name;
        }

        if ($propertyFetchNode->name instanceof Concat) {
            return '';
        }

        return (string) $propertyFetchNode->name;
    }
}
