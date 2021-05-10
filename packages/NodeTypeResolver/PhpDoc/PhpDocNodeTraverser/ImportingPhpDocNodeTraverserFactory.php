<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser;

use Rector\NodeTypeResolver\PhpDocNodeVisitor\NameImportingPhpDocNodeVisitor;
use RectorPrefix20210510\Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
final class ImportingPhpDocNodeTraverserFactory
{
    /**
     * @var NameImportingPhpDocNodeVisitor
     */
    private $nameImportingPhpDocNodeVisitor;
    public function __construct(NameImportingPhpDocNodeVisitor $nameImportingPhpDocNodeVisitor)
    {
        $this->nameImportingPhpDocNodeVisitor = $nameImportingPhpDocNodeVisitor;
    }
    public function create() : PhpDocNodeTraverser
    {
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->addPhpDocNodeVisitor($this->nameImportingPhpDocNodeVisitor);
        return $phpDocNodeTraverser;
    }
}
