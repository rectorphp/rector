<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\PhpDoc;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use RectorPrefix20220606\Rector\NodeTypeResolver\PhpDocNodeVisitor\UnderscoreRenamePhpDocNodeVisitor;
use RectorPrefix20220606\Rector\Renaming\ValueObject\PseudoNamespaceToNamespace;
use RectorPrefix20220606\Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20220606\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
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
