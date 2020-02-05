<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc;

use Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Node as PhpDocParserNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\Ast\PhpDocNodeTraverser;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\StaticTypeMapper;

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

    /**
     * @param string[] $excludedClasses
     */
    public function changeUnderscoreType(Node $node, string $namespacePrefix, array $excludedClasses): void
    {
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return;
        }

        $phpDocNode = $phpDocInfo->getPhpDocNode();
        $phpParserNode = $node;

        $this->phpDocNodeTraverser->traverseWithCallable($phpDocNode, function (PhpDocParserNode $node) use (
            $namespacePrefix,
            $excludedClasses,
            $phpParserNode
        ): PhpDocParserNode {
            if ($this->shouldSkip($node, $phpParserNode, $namespacePrefix, $excludedClasses)) {
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
        string $namespacePrefix,
        $excludedClasses
    ): bool {
        if (! $phpDocParserNode instanceof IdentifierTypeNode) {
            return true;
        }

        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($phpDocParserNode, $phpParserNode);
        if (! $staticType instanceof ObjectType) {
            return true;
        }

        if (! Strings::startsWith($staticType->getClassName(), $namespacePrefix)) {
            return true;
        }

        // excluded?
        return in_array($staticType->getClassName(), $excludedClasses, true);
    }
}
