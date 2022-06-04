<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PhpDocNodeVisitor;

use RectorPrefix20220604\Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\ObjectType;
use Rector\Renaming\ValueObject\PseudoNamespaceToNamespace;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20220604\Symplify\Astral\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class UnderscoreRenamePhpDocNodeVisitor extends \RectorPrefix20220604\Symplify\Astral\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor
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
    public function __construct(\Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\Renaming\ValueObject\PseudoNamespaceToNamespace $pseudoNamespaceToNamespace, \PhpParser\Node $phpNode)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->pseudoNamespaceToNamespace = $pseudoNamespaceToNamespace;
        $this->phpNode = $phpNode;
    }
    public function beforeTraverse(\PHPStan\PhpDocParser\Ast\Node $node) : void
    {
        $this->hasChanged = \false;
    }
    public function enterNode(\PHPStan\PhpDocParser\Ast\Node $node) : ?\PHPStan\PhpDocParser\Ast\Node
    {
        if (!$node instanceof \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode) {
            return null;
        }
        if ($this->shouldSkip($node, $this->phpNode, $this->pseudoNamespaceToNamespace)) {
            return null;
        }
        /** @var IdentifierTypeNode $node */
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($node, $this->phpNode);
        if (!$staticType instanceof \PHPStan\Type\ObjectType) {
            return null;
        }
        $this->hasChanged = \true;
        // change underscore to \\
        $slashedName = '\\' . \RectorPrefix20220604\Nette\Utils\Strings::replace($staticType->getClassName(), '#_#', '\\');
        return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode($slashedName);
    }
    public function hasChanged() : bool
    {
        return $this->hasChanged;
    }
    private function shouldSkip(\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode $identifierTypeNode, \PhpParser\Node $phpParserNode, \Rector\Renaming\ValueObject\PseudoNamespaceToNamespace $pseudoNamespaceToNamespace) : bool
    {
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($identifierTypeNode, $phpParserNode);
        if (!$staticType instanceof \PHPStan\Type\ObjectType) {
            return \true;
        }
        if (\strncmp($staticType->getClassName(), $pseudoNamespaceToNamespace->getNamespacePrefix(), \strlen($pseudoNamespaceToNamespace->getNamespacePrefix())) !== 0) {
            return \true;
        }
        // excluded?
        return \in_array($staticType->getClassName(), $pseudoNamespaceToNamespace->getExcludedClasses(), \true);
    }
}
