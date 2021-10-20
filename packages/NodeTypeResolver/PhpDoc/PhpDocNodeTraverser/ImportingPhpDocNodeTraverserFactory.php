<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser;

use Rector\NodeTypeResolver\PhpDocNodeVisitor\NameImportingPhpDocNodeVisitor;
use RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
final class ImportingPhpDocNodeTraverserFactory
{
    /**
     * @var \Rector\NodeTypeResolver\PhpDocNodeVisitor\NameImportingPhpDocNodeVisitor
     */
    private $nameImportingPhpDocNodeVisitor;
    public function __construct(\Rector\NodeTypeResolver\PhpDocNodeVisitor\NameImportingPhpDocNodeVisitor $nameImportingPhpDocNodeVisitor)
    {
        $this->nameImportingPhpDocNodeVisitor = $nameImportingPhpDocNodeVisitor;
    }
    public function create() : \RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser
    {
        $phpDocNodeTraverser = new \RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser();
        $phpDocNodeTraverser->addPhpDocNodeVisitor($this->nameImportingPhpDocNodeVisitor);
        return $phpDocNodeTraverser;
    }
}
