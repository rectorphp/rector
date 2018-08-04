<?php declare(strict_types=1);

namespace Rector\Rector\DomainDrivenDesign\ValueObjectRemover;

use PhpParser\Node;
use Rector\BetterPhpDocParser\NodeAnalyzer\DocBlockAnalyzer;
use Rector\BetterPhpDocParser\NodeAnalyzer\NamespaceAnalyzer;
use Rector\NodeTraverserQueue\BetterNodeFinder;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\ScopeToTypesResolver;
use Rector\Rector\AbstractRector;

abstract class AbstractValueObjectRemoverRector extends AbstractRector
{
    /**
     * @var string[]
     */
    protected $valueObjectsToSimpleTypes = [];

    /**
     * @var DocBlockAnalyzer
     */
    protected $docBlockAnalyzer;

    /**
     * @var NodeTypeResolver
     */
    protected $nodeTypeResolver;

    /**
     * @var BetterNodeFinder
     */
    protected $betterNodeFinder;

    /**
     * @var NamespaceAnalyzer
     */
    protected $namespaceAnalyzer;

    /**
     * @var ScopeToTypesResolver
     */
    protected $scopeToTypesResolver;

    /**
     * @param string[] $valueObjectsToSimpleTypes
     */
    public function __construct(
        array $valueObjectsToSimpleTypes,
        DocBlockAnalyzer $docBlockAnalyzer,
        NodeTypeResolver $nodeTypeResolver,
        BetterNodeFinder $betterNodeFinder,
        NamespaceAnalyzer $namespaceAnalyzer,
        ScopeToTypesResolver $scopeToTypesResolver
    ) {
        $this->valueObjectsToSimpleTypes = $valueObjectsToSimpleTypes;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->namespaceAnalyzer = $namespaceAnalyzer;
        $this->scopeToTypesResolver = $scopeToTypesResolver;
    }

    /**
     * @return string[]
     */
    protected function getValueObjects(): array
    {
        return array_keys($this->valueObjectsToSimpleTypes);
    }

    protected function matchNewType(Node $node): ?string
    {
        $nodeTypes = $this->nodeTypeResolver->resolve($node);
        foreach ($nodeTypes as $nodeType) {
            if (! isset($this->valueObjectsToSimpleTypes[$nodeType])) {
                continue;
            }

            return $this->valueObjectsToSimpleTypes[$nodeType];
        }

        return null;
    }

    /**
     * @return string[]|null
     */
    protected function matchOriginAndNewType(Node $node): ?array
    {
        $nodeTypes = $this->nodeTypeResolver->resolve($node);

        foreach ($nodeTypes as $nodeType) {
            if (! isset($this->valueObjectsToSimpleTypes[$nodeType])) {
                continue;
            }

            return [$nodeType, $this->valueObjectsToSimpleTypes[$nodeType]];
        }

        return null;
    }
}
