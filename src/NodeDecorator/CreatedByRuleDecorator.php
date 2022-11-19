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
        $createdByRule = $node->getAttribute(AttributeKey::CREATED_BY_RULE) ?? [];
        \end($createdByRule);
        $lastRectorRuleKey = \key($createdByRule);
        // empty array, insert
        if ($lastRectorRuleKey === null) {
            $node->setAttribute(AttributeKey::CREATED_BY_RULE, [$rectorClass]);
            return;
        }
        // consecutive, no need to refill
        if ($createdByRule[$lastRectorRuleKey] === $rectorClass) {
            return;
        }
        // filter out when exists, then append
        $createdByRule = \array_filter($createdByRule, static function (string $rectorRule) use($rectorClass) : bool {
            return $rectorRule !== $rectorClass;
        });
        $node->setAttribute(AttributeKey::CREATED_BY_RULE, \array_merge(\is_array($createdByRule) ? $createdByRule : \iterator_to_array($createdByRule), [$rectorClass]));
    }
}
