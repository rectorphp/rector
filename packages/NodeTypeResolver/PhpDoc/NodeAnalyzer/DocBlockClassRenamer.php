<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PhpParser\Node\Name;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser\RenamingPhpDocNodeVisitorFactory;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\ClassRenamePhpDocNodeVisitor;
use Rector\NodeTypeResolver\ValueObject\OldToNewType;
final class DocBlockClassRenamer
{
    /**
     * @var \Rector\NodeTypeResolver\PhpDocNodeVisitor\ClassRenamePhpDocNodeVisitor
     */
    private $classRenamePhpDocNodeVisitor;
    /**
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
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        $tags = $phpDocNode->getTags();
        foreach ($tags as $tag) {
            $tagValueNode = $tag->value;
            $tagName = $phpDocInfo->resolveNameForPhpDocTagValueNode($tagValueNode);
            if (!\is_string($tagName)) {
                continue;
            }
            $tagValues = $phpDocInfo->getTagsByName($tagName);
            foreach ($tagValues as $tagValue) {
                $name = new \PhpParser\Node\Name((string) $tagValue->value);
                if ($name->isSpecialClassName()) {
                    return;
                }
            }
        }
        $phpDocNodeTraverser = $this->renamingPhpDocNodeVisitorFactory->create();
        $this->classRenamePhpDocNodeVisitor->setOldToNewTypes($oldToNewTypes);
        $phpDocNodeTraverser->traverse($phpDocInfo->getPhpDocNode());
    }
}
