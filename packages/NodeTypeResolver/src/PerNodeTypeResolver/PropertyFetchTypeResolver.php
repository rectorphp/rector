<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
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

    public function __construct(TypeContext $typeContext)
    {
        $this->typeContext = $typeContext;
    }

    public function getNodeClass(): string
    {
        return PropertyFetch::class;
    }

    /**
     * @param PropertyFetch $propertyFetchNode
     */
    public function resolve(Node $propertyFetchNode): ?string
    {
        // e.g. $r->getParameters()[0]->name
        if ($propertyFetchNode->var instanceof ArrayDimFetch) {
            return $this->nodeTypeResolver->resolve($propertyFetchNode);
        }

        if ($propertyFetchNode->var instanceof New_) {
            return $this->nodeTypeResolver->resolve($propertyFetchNode->var);
        }

        if ($propertyFetchNode->var->name !== 'this') {
            return null;
        }

        $propertyName = $this->resolvePropertyName($propertyFetchNode);

        return $this->typeContext->getTypeForProperty($propertyName);
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
