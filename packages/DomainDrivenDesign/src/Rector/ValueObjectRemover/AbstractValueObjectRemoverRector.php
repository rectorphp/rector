<?php declare(strict_types=1);

namespace Rector\DomainDrivenDesign\Rector\ValueObjectRemover;

use PhpParser\Node;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\NamespaceAnalyzer;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\NamespaceAnalyzer as RectorNamespaceAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
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
        BetterNodeFinder $betterNodeFinder,
        RectorNamespaceAnalyzer $rectorNamespaceAnalyzer
    ) {
        $this->valueObjectsToSimpleTypes = $valueObjectsToSimpleTypes;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->namespaceAnalyzer = $rectorNamespaceAnalyzer;
    }

    protected function matchNewType(Node $node): ?string
    {
        foreach ($this->getTypes($node) as $nodeType) {
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
        foreach ($this->getTypes($node) as $nodeType) {
            if (! isset($this->valueObjectsToSimpleTypes[$nodeType])) {
                continue;
            }

            return [$nodeType, $this->valueObjectsToSimpleTypes[$nodeType]];
        }

        return null;
    }
}
