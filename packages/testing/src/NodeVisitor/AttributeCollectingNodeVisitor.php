<?php

declare(strict_types=1);

namespace Rector\Testing\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\Exception\ShouldNotHappenException;

final class AttributeCollectingNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var string
     */
    private $relevantAttribute;

    /**
     * @var mixed[]
     */
    private $attributes = [];

    public function setRelevantAttribute(string $relevantAttribute): void
    {
        $this->relevantAttribute = $relevantAttribute;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($this->relevantAttribute === null) {
            throw new ShouldNotHappenException();
        }

        if ($node instanceof Expression) {
            return null;
        }

        $attributes = $this->getFilteredAttributes($node);
        $this->attributes[] = array_merge([
            'node_class' => get_class($node),
        ], $attributes);

        return null;
    }

    /**
     * @return mixed[]
     */
    public function getCollectedAttributes(): array
    {
        return $this->attributes;
    }

    public function reset(): void
    {
        $this->attributes = [];
    }

    /**
     * @return mixed[]
     */
    private function getFilteredAttributes(Node $node): array
    {
        $attributes = $node->getAttributes();
        return array_intersect_key($attributes, array_flip([$this->relevantAttribute]));
    }
}
