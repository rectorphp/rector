<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PhpDoc;

use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser\UnderscorePhpDocNodeTraverserFactory;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\UnderscoreRenamePhpDocNodeVisitor;
use Rector\Renaming\ValueObject\PseudoNamespaceToNamespace;
final class PhpDocTypeRenamer
{
    /**
     * @var \Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser\UnderscorePhpDocNodeTraverserFactory
     */
    private $underscorePhpDocNodeTraverserFactory;
    /**
     * @var \Rector\NodeTypeResolver\PhpDocNodeVisitor\UnderscoreRenamePhpDocNodeVisitor
     */
    private $underscoreRenamePhpDocNodeVisitor;
    public function __construct(\Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser\UnderscorePhpDocNodeTraverserFactory $underscorePhpDocNodeTraverserFactory, \Rector\NodeTypeResolver\PhpDocNodeVisitor\UnderscoreRenamePhpDocNodeVisitor $underscoreRenamePhpDocNodeVisitor)
    {
        $this->underscorePhpDocNodeTraverserFactory = $underscorePhpDocNodeTraverserFactory;
        $this->underscoreRenamePhpDocNodeVisitor = $underscoreRenamePhpDocNodeVisitor;
    }
    public function changeUnderscoreType(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node $node, \Rector\Renaming\ValueObject\PseudoNamespaceToNamespace $pseudoNamespaceToNamespace) : void
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        $this->underscoreRenamePhpDocNodeVisitor->setPseudoNamespaceToNamespace($pseudoNamespaceToNamespace);
        $this->underscoreRenamePhpDocNodeVisitor->setCurrentPhpParserNode($node);
        $phpDocNodeTraverser = $this->underscorePhpDocNodeTraverserFactory->create();
        $phpDocNodeTraverser->traverse($phpDocNode);
    }
}
