<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\NodeTraverser;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\NameImportingPhpDocNodeVisitor;
final class DocBlockNameImporter
{
    /**
     * @readonly
     */
    private NameImportingPhpDocNodeVisitor $nameImportingPhpDocNodeVisitor;
    public function __construct(NameImportingPhpDocNodeVisitor $nameImportingPhpDocNodeVisitor)
    {
        $this->nameImportingPhpDocNodeVisitor = $nameImportingPhpDocNodeVisitor;
    }
    public function importNames(PhpDocNode $phpDocNode, Node $node): bool
    {
        if ($phpDocNode->children === []) {
            return \false;
        }
        $this->nameImportingPhpDocNodeVisitor->setCurrentNode($node);
        $phpDocNodeTraverser = new NodeTraverser([$this->nameImportingPhpDocNodeVisitor]);
        $phpDocNodeTraverser->traverse([$phpDocNode]);
        return $this->nameImportingPhpDocNodeVisitor->hasChanged();
    }
}
