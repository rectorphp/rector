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
    public function decorate($node, \PhpParser\Node $originalNode, string $rectorClass) : void
    {
        if ($node instanceof \PhpParser\Node) {
            $node = [$node];
        }
        foreach ($node as $singleNode) {
            $this->createByRule($singleNode, $rectorClass);
        }
        $this->createByRule($originalNode, $rectorClass);
    }
    private function createByRule(\PhpParser\Node $node, string $rectorClass) : void
    {
        $mergeCreatedByRule = \array_merge($node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CREATED_BY_RULE) ?? [], [$rectorClass]);
        $mergeCreatedByRule = \array_unique($mergeCreatedByRule);
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CREATED_BY_RULE, $mergeCreatedByRule);
    }
}
