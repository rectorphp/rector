<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\BetterReflection\Reflector\PropertyReflector;
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
            return $this->resolveTypesFromVariable($propertyFetchNode->var->var, $propertyName);
        }

        if ($propertyFetchNode->var instanceof New_) {
            return $this->nodeTypeResolver->resolve($propertyFetchNode->var);
        }

        /** @var Variable $variableNode */
        $variableNode = $propertyFetchNode->var;

        // e.g. $this->property
        if ($variableNode->name === 'this') {
            $propertyName = $this->resolvePropertyName($propertyFetchNode);

            return $this->typeContext->getTypesForProperty($propertyName);
        }

        return $this->resolveTypesFromVariable($variableNode, $propertyName);
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    private function resolvePropertyName(PropertyFetch $propertyFetchNode): string
    {
        if ($propertyFetchNode->name instanceof Variable) {
            return (string) $propertyFetchNode->name->name;
        }

        if ($propertyFetchNode->name instanceof Name || $propertyFetchNode->name instanceof Identifier) {
            return $propertyFetchNode->name->toString();
        }

        return '';
    }

    /**
     * @return string[]
     */
    private function resolveTypesFromVariable(Expr $exprNode, string $propertyName): array
    {
        $types = $this->nodeTypeResolver->resolve($exprNode);
        if (! $types) {
            return [];
        }

        $type = array_shift($types);

        $propertyType = $this->propertyReflector->getPropertyType($type, $propertyName);
        if (! $propertyType) {
            return [];
        }

        return [$propertyType];
    }
}
