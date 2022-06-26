<?php

declare (strict_types=1);
namespace Rector\Core\NodeDecorator;

use PhpParser\Node;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class CreatedByRuleDecorator
{
    /**
     * @param mixed[]|\PhpParser\Node $node
     */
    public function decorate($node, Node $originalNode, string $rectorClass) : void
    {
        if ($node instanceof Node) {
            $node = [$node];
        }
        foreach ($node as $singleNode) {
            if (\get_class($singleNode) === \get_class($originalNode)) {
                $this->createByRule($singleNode, $rectorClass);
            }
        }
        $this->createByRule($originalNode, $rectorClass);
    }
    private function createByRule(Node $node, string $rectorClass) : void
    {
        $mergeCreatedByRule = \array_merge($node->getAttribute(AttributeKey::CREATED_BY_RULE) ?? [], [$rectorClass]);
        $mergeCreatedByRule = \array_unique($mergeCreatedByRule);
        $node->setAttribute(AttributeKey::CREATED_BY_RULE, $mergeCreatedByRule);
    }
}
