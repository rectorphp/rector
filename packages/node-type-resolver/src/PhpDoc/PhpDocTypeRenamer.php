<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc;

use Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Node as PhpDocParserNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Renaming\ValueObject\PseudoNamespaceToNamespace;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

final class PhpDocTypeRenamer
{
    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    public function __construct(PhpDocNodeTraverser $phpDocNodeTraverser, StaticTypeMapper $staticTypeMapper)
    {
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
        $this->staticTypeMapper = $staticTypeMapper;
    }

    public function changeUnderscoreType(
        PhpDocInfo $phpDocInfo,
        Node $node,
        PseudoNamespaceToNamespace $pseudoNamespaceToNamespace
    ): void {
        $attributeAwarePhpDocNode = $phpDocInfo->getPhpDocNode();
        $phpParserNode = $node;

        $this->phpDocNodeTraverser->traverseWithCallable($attributeAwarePhpDocNode, '', function (
            PhpDocParserNode $node
        ) use ($pseudoNamespaceToNamespace, $phpParserNode, $phpDocInfo): PhpDocParserNode {
            if (! $node instanceof TypeNode) {
                return $node;
            }

            if ($this->shouldSkip($node, $phpParserNode, $pseudoNamespaceToNamespace)) {
                return $node;
            }

            /** @var IdentifierTypeNode $node */
            $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($node, $phpParserNode);
            if (! $staticType instanceof ObjectType) {
                return $node;
            }

            // change underscore to \\
            $slashedName = '\\' . Strings::replace($staticType->getClassName(), '#_#', '\\');
            $node->name = $slashedName;

            $phpDocInfo->markAsChanged();

            return $node;
        });
    }

    private function shouldSkip(
        TypeNode $typeNode,
        Node $phpParserNode,
        PseudoNamespaceToNamespace $pseudoNamespaceToNamespace
    ): bool {
        if (! $typeNode instanceof IdentifierTypeNode) {
            return true;
        }

        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($typeNode, $phpParserNode);
        if (! $staticType instanceof ObjectType) {
            return true;
        }

        if (! Strings::startsWith(
            $staticType->getClassName(),
            $pseudoNamespaceToNamespace->getNamespacePrefix()
        )) {
            return true;
        }

        // excluded?
        return in_array($staticType->getClassName(), $pseudoNamespaceToNamespace->getExcludedClasses(), true);
    }
}
