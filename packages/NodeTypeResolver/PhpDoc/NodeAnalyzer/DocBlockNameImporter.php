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
     * @var NameImportingPhpDocNodeVisitor
     */
    private $nameImportingPhpDocNodeVisitor;
    /**
     * @var ImportingPhpDocNodeTraverserFactory
     */
    private $importingPhpDocNodeTraverserFactory;
    public function __construct(NameImportingPhpDocNodeVisitor $nameImportingPhpDocNodeVisitor, ImportingPhpDocNodeTraverserFactory $importingPhpDocNodeTraverserFactory)
    {
        $this->nameImportingPhpDocNodeVisitor = $nameImportingPhpDocNodeVisitor;
        $this->importingPhpDocNodeTraverserFactory = $importingPhpDocNodeTraverserFactory;
    }
    public function importNames(PhpDocNode $phpDocNode, Node $node) : void
    {
        if ($phpDocNode->children === []) {
            return;
        }
        $this->nameImportingPhpDocNodeVisitor->setCurrentNode($node);
        $phpDocNodeTraverser = $this->importingPhpDocNodeTraverserFactory->create();
        $phpDocNodeTraverser->traverse($phpDocNode);
    }
}
