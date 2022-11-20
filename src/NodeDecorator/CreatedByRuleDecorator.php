<?php

declare (strict_types=1);
namespace Rector\Core\NodeDecorator;

use PhpParser\Node;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class CreatedByRuleDecorator
{
    /**
     * @param mixed[]|\PhpParser\Node $node
     * @param class-string<RectorInterface> $rectorClass
     */
    public function decorate($node, Node $originalNode, string $rectorClass) : void
    {
        if ($node instanceof Node && $node === $originalNode) {
            $this->createByRule($node, $rectorClass);
            return;
        }
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
    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    private function createByRule(Node $node, string $rectorClass) : void
    {
        /** @var class-string<RectorInterface>[] $createdByRule */
        $createdByRule = $node->getAttribute(AttributeKey::CREATED_BY_RULE) ?? [];
        // empty array, insert
        if ($createdByRule === []) {
            $node->setAttribute(AttributeKey::CREATED_BY_RULE, [$rectorClass]);
            return;
        }
        // consecutive, no need to refill
        if (\end($createdByRule) === $rectorClass) {
            return;
        }
        // filter out when exists, then append
        $createdByRule = \array_filter($createdByRule, static function (string $rectorRule) use($rectorClass) : bool {
            return $rectorRule !== $rectorClass;
        });
        $node->setAttribute(AttributeKey::CREATED_BY_RULE, \array_merge($createdByRule, [$rectorClass]));
    }
}
