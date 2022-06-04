<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\NameImportingPhpDocNodeVisitor;
use RectorPrefix20220604\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
final class DocBlockNameImporter
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PhpDocNodeVisitor\NameImportingPhpDocNodeVisitor
     */
    private $nameImportingPhpDocNodeVisitor;
    public function __construct(\Rector\NodeTypeResolver\PhpDocNodeVisitor\NameImportingPhpDocNodeVisitor $nameImportingPhpDocNodeVisitor)
    {
        $this->nameImportingPhpDocNodeVisitor = $nameImportingPhpDocNodeVisitor;
    }
    public function importNames(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDocNode, \PhpParser\Node $node) : void
    {
        if ($phpDocNode->children === []) {
            return;
        }
        $this->nameImportingPhpDocNodeVisitor->setCurrentNode($node);
        $phpDocNodeTraverser = new \RectorPrefix20220604\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser();
        $phpDocNodeTraverser->addPhpDocNodeVisitor($this->nameImportingPhpDocNodeVisitor);
        $phpDocNodeTraverser->traverse($phpDocNode);
    }
}
