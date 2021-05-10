<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser\ImportingPhpDocNodeTraverserFactory;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\NameImportingPhpDocNodeVisitor;
final class DocBlockNameImporter
{
    /**
     * @var \Rector\NodeTypeResolver\PhpDocNodeVisitor\NameImportingPhpDocNodeVisitor
     */
    private $nameImportingPhpDocNodeVisitor;
    /**
     * @var \Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser\ImportingPhpDocNodeTraverserFactory
     */
    private $importingPhpDocNodeTraverserFactory;
    public function __construct(\Rector\NodeTypeResolver\PhpDocNodeVisitor\NameImportingPhpDocNodeVisitor $nameImportingPhpDocNodeVisitor, \Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser\ImportingPhpDocNodeTraverserFactory $importingPhpDocNodeTraverserFactory)
    {
        $this->nameImportingPhpDocNodeVisitor = $nameImportingPhpDocNodeVisitor;
        $this->importingPhpDocNodeTraverserFactory = $importingPhpDocNodeTraverserFactory;
    }
    public function importNames(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDocNode, \PhpParser\Node $node) : void
    {
        if ($phpDocNode->children === []) {
            return;
        }
        $this->nameImportingPhpDocNodeVisitor->setCurrentNode($node);
        $phpDocNodeTraverser = $this->importingPhpDocNodeTraverserFactory->create();
        $phpDocNodeTraverser->traverse($phpDocNode);
    }
}
