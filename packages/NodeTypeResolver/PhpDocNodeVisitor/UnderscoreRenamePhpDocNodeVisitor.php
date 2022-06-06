<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\PhpDocNodeVisitor;

use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Renaming\ValueObject\PseudoNamespaceToNamespace;
use RectorPrefix20220606\Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20220606\Symplify\Astral\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class UnderscoreRenamePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    /**
     * @var bool
     */
    private $hasChanged = \false;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\Renaming\ValueObject\PseudoNamespaceToNamespace
     */
    private $pseudoNamespaceToNamespace;
    /**
     * @readonly
     * @var \PhpParser\Node
     */
    private $phpNode;
    public function __construct(StaticTypeMapper $staticTypeMapper, PseudoNamespaceToNamespace $pseudoNamespaceToNamespace, \RectorPrefix20220606\PhpParser\Node $phpNode)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->pseudoNamespaceToNamespace = $pseudoNamespaceToNamespace;
        $this->phpNode = $phpNode;
    }
    public function beforeTraverse(Node $node) : void
    {
        $this->hasChanged = \false;
    }
    public function enterNode(Node $node) : ?Node
    {
        if (!$node instanceof IdentifierTypeNode) {
            return null;
        }
        if ($this->shouldSkip($node, $this->phpNode, $this->pseudoNamespaceToNamespace)) {
            return null;
        }
        /** @var IdentifierTypeNode $node */
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($node, $this->phpNode);
        if (!$staticType instanceof ObjectType) {
            return null;
        }
        $this->hasChanged = \true;
        // change underscore to \\
        $slashedName = '\\' . Strings::replace($staticType->getClassName(), '#_#', '\\');
        return new IdentifierTypeNode($slashedName);
    }
    public function hasChanged() : bool
    {
        return $this->hasChanged;
    }
    private function shouldSkip(IdentifierTypeNode $identifierTypeNode, \RectorPrefix20220606\PhpParser\Node $phpParserNode, PseudoNamespaceToNamespace $pseudoNamespaceToNamespace) : bool
    {
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($identifierTypeNode, $phpParserNode);
        if (!$staticType instanceof ObjectType) {
            return \true;
        }
        if (\strncmp($staticType->getClassName(), $pseudoNamespaceToNamespace->getNamespacePrefix(), \strlen($pseudoNamespaceToNamespace->getNamespacePrefix())) !== 0) {
            return \true;
        }
        // excluded?
        return \in_array($staticType->getClassName(), $pseudoNamespaceToNamespace->getExcludedClasses(), \true);
    }
}
