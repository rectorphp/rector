<?php

declare(strict_types=1);

namespace Rector\Testing\Node;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use Rector\Testing\NodeVisitor\AttributeCollectingNodeVisitor;

final class NodeAttributeExtractor
{
    /**
     * @var AttributeCollectingNodeVisitor
     */
    private $attributeCollectingNodeVisitor;

    public function __construct(AttributeCollectingNodeVisitor $attributeCollectingNodeVisitor)
    {
        $this->attributeCollectingNodeVisitor = $attributeCollectingNodeVisitor;
    }

    /**
     * @param Node[] $nodes
     * @return mixed[]
     */
    public function extract(array $nodes, string $relevantAttribute): array
    {
        $this->attributeCollectingNodeVisitor->reset();
        $this->attributeCollectingNodeVisitor->setRelevantAttribute($relevantAttribute);

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->attributeCollectingNodeVisitor);
        $nodeTraverser->traverse($nodes);

        return $this->attributeCollectingNodeVisitor->getCollectedAttributes();
    }
}
