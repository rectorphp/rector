<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser;

use Rector\NodeTypeResolver\PhpDocNodeVisitor\NameImportingPhpDocNodeVisitor;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

final class ImportingPhpDocNodeTraverserFactory
{
    public function __construct(
        private NameImportingPhpDocNodeVisitor $nameImportingPhpDocNodeVisitor
    ) {
    }

    public function create(): PhpDocNodeTraverser
    {
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->addPhpDocNodeVisitor($this->nameImportingPhpDocNodeVisitor);

        return $phpDocNodeTraverser;
    }
}
