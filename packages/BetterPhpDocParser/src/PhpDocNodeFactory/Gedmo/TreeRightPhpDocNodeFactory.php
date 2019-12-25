<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Gedmo;

use Gedmo\Mapping\Annotation\TreeRight;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\TreeRightTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractBasicPropertyPhpDocNodeFactory;

final class TreeRightPhpDocNodeFactory extends AbstractBasicPropertyPhpDocNodeFactory
{
    public function getClass(): string
    {
        return TreeRight::class;
    }

    /**
     * @return TreeRightTagValueNode|null
     */
    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        return $this->createFromNode($node);
    }

    protected function getTagValueNodeClass(): string
    {
        return TreeRightTagValueNode::class;
    }
}
