<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc;

use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\UnderscoreRenamePhpDocNodeVisitor;
use Rector\Renaming\ValueObject\PseudoNamespaceToNamespace;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;

final class PhpDocTypeRenamer
{
    public function __construct(
        private readonly StaticTypeMapper $staticTypeMapper,
    ) {
    }

    public function changeUnderscoreType(
        PhpDocInfo $phpDocInfo,
        Node $node,
        PseudoNamespaceToNamespace $pseudoNamespaceToNamespace
    ): bool {
        $phpDocNode = $phpDocInfo->getPhpDocNode();

        $underscoreRenamePhpDocNodeVisitor = new UnderscoreRenamePhpDocNodeVisitor(
            $this->staticTypeMapper,
            $pseudoNamespaceToNamespace,
            $node,
        );

        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->addPhpDocNodeVisitor($underscoreRenamePhpDocNodeVisitor);
        $phpDocNodeTraverser->traverse($phpDocNode);

        // has changed?
        return $underscoreRenamePhpDocNodeVisitor->hasChanged();
    }
}
