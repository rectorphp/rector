<?php declare(strict_types=1);

namespace Rector\Rector\DomainDrivenDesign\ValueObjectRemover;

use PhpParser\Node;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\NamespaceAnalyzer;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\NamespaceAnalyzer as RectorNamespaceAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Utils\BetterNodeFinder;

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
     * @param string[] $valueObjectsToSimpleTypes
     */
    public function __construct(
        array $valueObjectsToSimpleTypes,
        DocBlockAnalyzer $docBlockAnalyzer,
        NodeTypeResolver $nodeTypeResolver,
        BetterNodeFinder $betterNodeFinder,
        RectorNamespaceAnalyzer $rectorNamespaceAnalyzer
    ) {
        $this->valueObjectsToSimpleTypes = $valueObjectsToSimpleTypes;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->namespaceAnalyzer = $rectorNamespaceAnalyzer;
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
