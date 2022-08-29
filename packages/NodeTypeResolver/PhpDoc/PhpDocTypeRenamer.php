<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PhpDoc;

use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\UnderscoreRenamePhpDocNodeVisitor;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
use Rector\Renaming\ValueObject\PseudoNamespaceToNamespace;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class PhpDocTypeRenamer
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function changeUnderscoreType(PhpDocInfo $phpDocInfo, Node $node, PseudoNamespaceToNamespace $pseudoNamespaceToNamespace) : bool
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        $underscoreRenamePhpDocNodeVisitor = new UnderscoreRenamePhpDocNodeVisitor($this->staticTypeMapper, $pseudoNamespaceToNamespace, $node);
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->addPhpDocNodeVisitor($underscoreRenamePhpDocNodeVisitor);
        $phpDocNodeTraverser->traverse($phpDocNode);
        // has changed?
        return $underscoreRenamePhpDocNodeVisitor->hasChanged();
    }
}
