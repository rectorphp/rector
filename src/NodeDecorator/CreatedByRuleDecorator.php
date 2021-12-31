<?php

declare (strict_types=1);
namespace Rector\Core\NodeDecorator;

use PhpParser\Node;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class CreatedByRuleDecorator
{
    public function decorate(\PhpParser\Node $node, string $rectorClass) : void
    {
        $mergeCreatedByRule = \array_merge($node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CREATED_BY_RULE) ?? [], [$rectorClass]);
        $mergeCreatedByRule = \array_unique($mergeCreatedByRule);
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CREATED_BY_RULE, $mergeCreatedByRule);
    }
}
