<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser\ImportingPhpDocNodeTraverserFactory;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\NameImportingPhpDocNodeVisitor;

final class DocBlockNameImporter
{
    public function __construct(
        private NameImportingPhpDocNodeVisitor $nameImportingPhpDocNodeVisitor,
        private ImportingPhpDocNodeTraverserFactory $importingPhpDocNodeTraverserFactory
    ) {
    }

    public function importNames(PhpDocNode $phpDocNode, Node $node): void
    {
        if ($phpDocNode->children === []) {
            return;
        }

        $this->nameImportingPhpDocNodeVisitor->setCurrentNode($node);

        $phpDocNodeTraverser = $this->importingPhpDocNodeTraverserFactory->create();
        $phpDocNodeTraverser->traverse($phpDocNode);
    }
}
