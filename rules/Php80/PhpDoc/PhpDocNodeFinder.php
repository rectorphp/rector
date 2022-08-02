<?php

declare (strict_types=1);
namespace Rector\Php80\PhpDoc;

use PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix202208\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
final class PhpDocNodeFinder
{
    /**
     * @api
     *
     * @template TNode as Node
     * @param class-string<TNode> $nodeType
     * @return TNode[]
     */
    public function findByType(Node $node, string $nodeType) : array
    {
        $foundNodes = [];
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($node, '', static function (Node $node) use(&$foundNodes, $nodeType) {
            if (!\is_a($node, $nodeType, \true)) {
                return null;
            }
            $foundNodes[] = $node;
            return null;
        });
        return $foundNodes;
    }
}
