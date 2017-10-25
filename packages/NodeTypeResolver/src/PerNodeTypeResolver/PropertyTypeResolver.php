<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\TypeContext;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;
use Rector\ReflectionDocBlock\NodeAnalyzer\NamespaceAnalyzer;

final class PropertyTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var TypeContext
     */
    private $typeContext;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var NamespaceAnalyzer
     */
    private $namespaceAnalyzer;

    public function __construct(
        TypeContext $typeContext,
        DocBlockAnalyzer $docBlockAnalyzer,
        NamespaceAnalyzer $namespaceAnalyzer
    ) {
        $this->typeContext = $typeContext;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->namespaceAnalyzer = $namespaceAnalyzer;
    }

    public function getNodeClass(): string
    {
        return Property::class;
    }

    /**
     * @param Property $propertyNode
     * @return string[]
     */
    public function resolve(Node $propertyNode): array
    {
        $propertyName = $propertyNode->props[0]->name->toString();
        $propertyTypes = $this->typeContext->getTypesForProperty($propertyName);
        if ($propertyTypes) {
            return $propertyTypes;
        }

        $propertyTypes = $this->docBlockAnalyzer->getAnnotationFromNode($propertyNode, 'var');

        $propertyTypes = $this->namespaceAnalyzer->resolveTypeToFullyQualified($propertyTypes, $propertyNode);

        $this->typeContext->addPropertyTypes($propertyName, [$propertyTypes]);

        return [$propertyTypes];
    }
}
