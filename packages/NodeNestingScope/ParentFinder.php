<?php

declare (strict_types=1);
namespace Rector\NodeNestingScope;

use PhpParser\Node;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20211020\Webmozart\Assert\Assert;
final class ParentFinder
{
    /**
     * @template T of \PhpParser\Node
     * @param array<class-string<T>> $types
     * @return T|null
     */
    public function findByTypes(\PhpParser\Node $node, array $types) : ?\PhpParser\Node
    {
        \RectorPrefix20211020\Webmozart\Assert\Assert::allIsAOf($types, \PhpParser\Node::class);
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node) {
            return null;
        }
        do {
            foreach ($types as $type) {
                if (\is_a($parent, $type, \true)) {
                    return $parent;
                }
            }
            if ($parent === null) {
                return null;
            }
        } while ($parent = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE));
        return null;
    }
}
