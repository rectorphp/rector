<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Gedmo;

use Gedmo\Mapping\Annotation\TreeRoot;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\TreeRootTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractBasicPropertyPhpDocNodeFactory;

final class TreeRootPhpDocNodeFactory extends AbstractBasicPropertyPhpDocNodeFactory
{
    public function getClass(): string
    {
        return TreeRoot::class;
    }

    /**
     * @return TreeRootTagValueNode|null
     */
    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        return $this->createFromPropertyNode($node);
    }

    protected function getTagValueNodeClass(): string
    {
        return TreeRootTagValueNode::class;
    }
}
