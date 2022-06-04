<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PhpDoc;

use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\UnderscoreRenamePhpDocNodeVisitor;
use Rector\Renaming\ValueObject\PseudoNamespaceToNamespace;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20220604\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
final class PhpDocTypeRenamer
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(\Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function changeUnderscoreType(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node $node, \Rector\Renaming\ValueObject\PseudoNamespaceToNamespace $pseudoNamespaceToNamespace) : bool
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        $underscoreRenamePhpDocNodeVisitor = new \Rector\NodeTypeResolver\PhpDocNodeVisitor\UnderscoreRenamePhpDocNodeVisitor($this->staticTypeMapper, $pseudoNamespaceToNamespace, $node);
        $phpDocNodeTraverser = new \RectorPrefix20220604\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser();
        $phpDocNodeTraverser->addPhpDocNodeVisitor($underscoreRenamePhpDocNodeVisitor);
        $phpDocNodeTraverser->traverse($phpDocNode);
        // has changed?
        return $underscoreRenamePhpDocNodeVisitor->hasChanged();
    }
}
