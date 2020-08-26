<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc;

use Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Node as PhpDocParserNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\Ast\PhpDocNodeTraverser;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Generic\ValueObject\NamespacePrefixWithExcludedClasses;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\StaticTypeMapper;

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
        Node $node,
        NamespacePrefixWithExcludedClasses $namespacePrefixWithExcludedClasses
    ): void {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return;
        }

        $attributeAwarePhpDocNode = $phpDocInfo->getPhpDocNode();
        $phpParserNode = $node;

        $this->phpDocNodeTraverser->traverseWithCallable($attributeAwarePhpDocNode, '', function (
            PhpDocParserNode $node
        ) use ($namespacePrefixWithExcludedClasses, $phpParserNode): PhpDocParserNode {
            if ($this->shouldSkip($node, $phpParserNode, $namespacePrefixWithExcludedClasses)) {
                return $node;
            }

            /** @var IdentifierTypeNode $node */
            $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($node, $phpParserNode);

            // change underscore to \\
            /** @var ObjectType $staticType */
            $slashedName = '\\' . Strings::replace($staticType->getClassName(), '#_#', '\\');
            $node->name = $slashedName;

            return $node;
        });
    }

    private function shouldSkip(
        PhpDocParserNode $phpDocParserNode,
        Node $phpParserNode,
        NamespacePrefixWithExcludedClasses $namespacePrefixWithExcludedClasses
    ): bool {
        if (! $phpDocParserNode instanceof IdentifierTypeNode) {
            return true;
        }

        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($phpDocParserNode, $phpParserNode);
        if (! $staticType instanceof ObjectType) {
            return true;
        }

        if (! Strings::startsWith(
            $staticType->getClassName(),
            $namespacePrefixWithExcludedClasses->getNamespacePrefix()
        )) {
            return true;
        }

        // excluded?
        return in_array($staticType->getClassName(), $namespacePrefixWithExcludedClasses->getExcludedClasses(), true);
    }
}
