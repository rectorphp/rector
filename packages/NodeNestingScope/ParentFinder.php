<?php

declare(strict_types=1);

namespace Rector\NodeNestingScope;

use PhpParser\Node;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Webmozart\Assert\Assert;

final class ParentFinder
{
    /**
     * @template T of \PhpParser\Node
     * @param array<class-string<T>> $types
     * @return T|null
     */
    public function findByTypes(Node $node, array $types): ?Node
    {
        Assert::allIsAOf($types, Node::class);

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Node) {
            return null;
        }

        do {
            foreach ($types as $type) {
                if (is_a($parent, $type, true)) {
                    return $parent;
                }
            }

            if ($parent === null) {
                return null;
            }
        } while ($parent = $parent->getAttribute(AttributeKey::PARENT_NODE));

        return null;
    }
}
