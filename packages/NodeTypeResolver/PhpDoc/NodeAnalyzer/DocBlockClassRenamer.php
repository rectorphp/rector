<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser\RenamingPhpDocNodeVisitorFactory;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\ClassRenamePhpDocNodeVisitor;
use Rector\NodeTypeResolver\ValueObject\OldToNewType;

final class DocBlockClassRenamer
{
    public function __construct(
        private ClassRenamePhpDocNodeVisitor $classRenamePhpDocNodeVisitor,
        private RenamingPhpDocNodeVisitorFactory $renamingPhpDocNodeVisitorFactory
    ) {
    }

    /**
     * @param OldToNewType[] $oldToNewTypes
     */
    public function renamePhpDocType(PhpDocInfo $phpDocInfo, array $oldToNewTypes): void
    {
        if ($oldToNewTypes === []) {
            return;
        }

        $phpDocNodeTraverser = $this->renamingPhpDocNodeVisitorFactory->create();
        $this->classRenamePhpDocNodeVisitor->setOldToNewTypes($oldToNewTypes);

        $phpDocNodeTraverser->traverse($phpDocInfo->getPhpDocNode());
    }
}
