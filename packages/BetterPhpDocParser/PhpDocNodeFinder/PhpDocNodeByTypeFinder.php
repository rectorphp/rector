<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocNodeFinder;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use RectorPrefix20210701\Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
/**
 * @template TNode as \PHPStan\PhpDocParser\Ast\Node
 */
final class PhpDocNodeByTypeFinder
{
    /**
     * @param class-string<TNode> $desiredType
     * @return array<TNode>
     */
    public function findByType(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDocNode, string $desiredType) : array
    {
        $phpDocNodeTraverser = new \RectorPrefix20210701\Symplify\SimplePhpDocParser\PhpDocNodeTraverser();
        $foundNodes = [];
        $phpDocNodeTraverser->traverseWithCallable($phpDocNode, '', function ($node) use(&$foundNodes, $desiredType) {
            if (!\is_a($node, $desiredType, \true)) {
                return $node;
            }
            /** @var TNode $node */
            $foundNodes[] = $node;
            return $node;
        });
        return $foundNodes;
    }
}
