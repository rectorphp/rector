<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDocNodeVisitor;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\ObjectType;
use Rector\Renaming\ValueObject\PseudoNamespaceToNamespace;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\Astral\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;

final class UnderscoreRenamePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    private bool $hasChanged = false;

    public function __construct(
        private readonly StaticTypeMapper $staticTypeMapper,
        private readonly PseudoNamespaceToNamespace $pseudoNamespaceToNamespace,
        private readonly \PhpParser\Node $phpNode,
    ) {
    }

    public function beforeTraverse(Node $node): void
    {
        $this->hasChanged = false;
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof IdentifierTypeNode) {
            return null;
        }

        if ($this->shouldSkip($node, $this->phpNode, $this->pseudoNamespaceToNamespace)) {
            return null;
        }

        /** @var IdentifierTypeNode $node */
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($node, $this->phpNode);
        if (! $staticType instanceof ObjectType) {
            return null;
        }

        $this->hasChanged = true;

        // change underscore to \\
        $slashedName = '\\' . Strings::replace($staticType->getClassName(), '#_#', '\\');
        return new IdentifierTypeNode($slashedName);
    }

    public function hasChanged(): bool
    {
        return $this->hasChanged;
    }

    private function shouldSkip(
        IdentifierTypeNode $identifierTypeNode,
        \PhpParser\Node $phpParserNode,
        PseudoNamespaceToNamespace $pseudoNamespaceToNamespace
    ): bool {
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType(
            $identifierTypeNode,
            $phpParserNode
        );

        if (! $staticType instanceof ObjectType) {
            return true;
        }

        if (! \str_starts_with($staticType->getClassName(), $pseudoNamespaceToNamespace->getNamespacePrefix())) {
            return true;
        }

        // excluded?
        return in_array($staticType->getClassName(), $pseudoNamespaceToNamespace->getExcludedClasses(), true);
    }
}
