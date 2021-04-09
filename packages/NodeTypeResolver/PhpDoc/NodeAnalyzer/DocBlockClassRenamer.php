<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\ClassRenamePhpDocNodeVisitor;
use Rector\NodeTypeResolver\ValueObject\OldToNewType;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

final class DocBlockClassRenamer
{
    /**
     * @var ClassRenamePhpDocNodeVisitor
     */
    private $classRenamePhpDocNodeVisitor;

    public function __construct(ClassRenamePhpDocNodeVisitor $classRenamePhpDocNodeVisitor)
    {
        $this->classRenamePhpDocNodeVisitor = $classRenamePhpDocNodeVisitor;
    }

    /**
     * @param OldToNewType[] $oldToNewTypes
     */
    public function renamePhpDocType(PhpDocInfo $phpDocInfo, array $oldToNewTypes): void
    {
        if ($oldToNewTypes === []) {
            return;
        }

        $phpDocNodeTraverser = new PhpDocNodeTraverser();

        $this->classRenamePhpDocNodeVisitor->setOldToNewTypes($oldToNewTypes);
        $phpDocNodeTraverser->addPhpDocNodeVisitor($this->classRenamePhpDocNodeVisitor);
        $phpDocNodeTraverser->traverse($phpDocInfo->getPhpDocNode());
    }
}
