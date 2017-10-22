<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Property;
use Rector\NodeAnalyzer\DocBlockAnalyzer;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\TypeContext;

final class PropertyTypeResolver implements PerNodeTypeResolverInterface, NodeTypeResolverAwareInterface
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
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    public function __construct(TypeContext $typeContext, DocBlockAnalyzer $docBlockAnalyzer)
    {
        $this->typeContext = $typeContext;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
    }

    public function getNodeClass(): string
    {
        return Property::class;
    }

    /**
     * @param Property $propertyNode
     */
    public function resolve(Node $propertyNode): ?string
    {
        // return if has one - @todo uncomment later after NodeResolver cleanup
//        if ($propertyNode->getAttribute(Attribute::TYPE)) {
//            return $propertyNode->getAttribute(Attribute::TYPE);
//        }

        $varType = $this->docBlockAnalyzer->getAnnotationFromNode($propertyNode, 'var');


        dump($propertyNode);
        die;

        $propertyName = $this->resolvePropertyName($propertyNode);

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
