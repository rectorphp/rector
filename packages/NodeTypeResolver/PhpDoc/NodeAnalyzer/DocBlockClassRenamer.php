<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser\RenamingPhpDocNodeVisitorFactory;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\ClassRenamePhpDocNodeVisitor;
use Rector\NodeTypeResolver\ValueObject\OldToNewType;
final class DocBlockClassRenamer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PhpDocNodeVisitor\ClassRenamePhpDocNodeVisitor
     */
    private $classRenamePhpDocNodeVisitor;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser\RenamingPhpDocNodeVisitorFactory
     */
    private $renamingPhpDocNodeVisitorFactory;
    public function __construct(\Rector\NodeTypeResolver\PhpDocNodeVisitor\ClassRenamePhpDocNodeVisitor $classRenamePhpDocNodeVisitor, \Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser\RenamingPhpDocNodeVisitorFactory $renamingPhpDocNodeVisitorFactory)
    {
        $this->classRenamePhpDocNodeVisitor = $classRenamePhpDocNodeVisitor;
        $this->renamingPhpDocNodeVisitorFactory = $renamingPhpDocNodeVisitorFactory;
    }
    /**
     * @param OldToNewType[] $oldToNewTypes
     */
    public function renamePhpDocType(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, array $oldToNewTypes) : void
    {
        if ($oldToNewTypes === []) {
            return;
        }
        $phpDocNodeTraverser = $this->renamingPhpDocNodeVisitorFactory->create();
        $this->classRenamePhpDocNodeVisitor->setOldToNewTypes($oldToNewTypes);
        $phpDocNodeTraverser->traverse($phpDocInfo->getPhpDocNode());
    }
}
