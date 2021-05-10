<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser;

use Rector\NodeTypeResolver\PhpDocNodeVisitor\UnderscoreRenamePhpDocNodeVisitor;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

final class UnderscorePhpDocNodeTraverserFactory
{
    public function __construct(
        private UnderscoreRenamePhpDocNodeVisitor $underscoreRenamePhpDocNodeVisitor
    ) {
    }

    public function create(): PhpDocNodeTraverser
    {
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->addPhpDocNodeVisitor($this->underscoreRenamePhpDocNodeVisitor);

        return $phpDocNodeTraverser;
    }
}
