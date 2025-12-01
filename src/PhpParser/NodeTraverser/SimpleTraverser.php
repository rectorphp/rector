<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class SimpleTraverser
{
    /**
     * @param Node[]|Node $nodesOrNode
     * @param AttributeKey::* $attributeKey
     */
    public static function decorateWithTrueAttribute($nodesOrNode, string $attributeKey): void
    {
        $callableNodeVisitor = new class($attributeKey) extends NodeVisitorAbstract
        {
            /**
             * @readonly
             */
            private string $attributeKey;
            public function __construct(string $attributeKey)
            {
                $this->attributeKey = $attributeKey;
            }
            public function enterNode(Node $node)
            {
                $node->setAttribute($this->attributeKey, \true);
                return null;
            }
        };
        $nodeTraverser = new NodeTraverser($callableNodeVisitor);
        $nodes = $nodesOrNode instanceof Node ? [$nodesOrNode] : $nodesOrNode;
        $nodeTraverser->traverse($nodes);
    }
}
