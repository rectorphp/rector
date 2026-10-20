<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\ClassRenamePhpDocNodeVisitor;
use Rector\NodeTypeResolver\ValueObject\OldToNewType;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
final class DocBlockClassRenamer
{
    /**
     * @readonly
     */
    private ClassRenamePhpDocNodeVisitor $classRenamePhpDocNodeVisitor;
    /**
     * @readonly
     */
    private PhpDocNodeTraverser $phpDocNodeTraverser;
    public function __construct(ClassRenamePhpDocNodeVisitor $classRenamePhpDocNodeVisitor)
    {
        $this->classRenamePhpDocNodeVisitor = $classRenamePhpDocNodeVisitor;
        // the traverser + visitor are reusable across calls, so build them once
        $this->phpDocNodeTraverser = new PhpDocNodeTraverser();
        $this->phpDocNodeTraverser->addPhpDocNodeVisitor($this->classRenamePhpDocNodeVisitor);
    }
    /**
     * @param OldToNewType[] $oldToNewTypes
     */
    public function renamePhpDocType(PhpDocInfo $phpDocInfo, array $oldToNewTypes, Node $currentPhpNode): bool
    {
        if ($oldToNewTypes === []) {
            return \false;
        }
        $this->classRenamePhpDocNodeVisitor->setCurrentPhpNode($currentPhpNode);
        $this->classRenamePhpDocNodeVisitor->setOldToNewTypes($oldToNewTypes);
        $this->phpDocNodeTraverser->traverse($phpDocInfo->getPhpDocNode());
        return $this->classRenamePhpDocNodeVisitor->hasChanged();
    }
}
