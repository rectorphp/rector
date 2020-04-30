<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Gedmo;

use Gedmo\Mapping\Annotation\TreeLevel;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\TreeLevelTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractBasicPropertyPhpDocNodeFactory;

final class TreeLevelPhpDocNodeFactory extends AbstractBasicPropertyPhpDocNodeFactory
{
    public function getClass(): string
    {
        return TreeLevel::class;
    }

    /**
     * @return TreeLevelTagValueNode|null
     */
    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        return $this->createFromPropertyNode($node);
    }

    protected function getTagValueNodeClass(): string
    {
        return TreeLevelTagValueNode::class;
    }
}
