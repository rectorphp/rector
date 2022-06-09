<?php

declare (strict_types=1);
namespace Rector\Php80\PhpDoc;

use PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20220609\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
final class PhpDocNodeFinder
{
    /**
     * @template TNode as Node
     * @param class-string<TNode> $nodeType
     * @return TNode[]
     */
    public function findByType(Node $node, string $nodeType) : array
    {
        $foundNodes = [];
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($node, '', function (Node $node) use(&$foundNodes, $nodeType) {
            if (!\is_a($node, $nodeType, \true)) {
                return null;
            }
            $foundNodes[] = $node;
            return null;
        });
        return $foundNodes;
    }
}
