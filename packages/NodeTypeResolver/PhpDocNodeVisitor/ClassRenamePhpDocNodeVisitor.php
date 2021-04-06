<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;

final class ClassRenamePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    /**
     * @var array<string, string>
     */
    private $oldToNewClasses = [];

    public function __construct(StaticTypeMapper $staticTypeMapper, CurrentNodeProvider $currentNodeProvider)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->currentNodeProvider = $currentNodeProvider;
    }

    public function beforeTraverse(Node $node): void
    {
        if ($this->oldToNewClasses === []) {
            throw new ShouldNotHappenException('Configure "$oldToNewClasses" first');
        }
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof IdentifierTypeNode) {
            return null;
        }

        $phpParserNode = $this->currentNodeProvider->getNode();
        if ($phpParserNode === null) {
            throw new ShouldNotHappenException();
        }

        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($node, $phpParserNode);

        // make sure to compare FQNs
        if ($staticType instanceof ShortenedObjectType) {
            $staticType = new ObjectType($staticType->getFullyQualifiedName());
        }

        foreach ($this->oldToNewClasses as $oldClass => $newClass) {
            $oldType = new ObjectType($oldClass);
            $newType = new FullyQualifiedObjectType($newClass);

            if (! $staticType->equals($oldType)) {
                continue;
            }

            $newTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType);

            $parentType = $node->getAttribute(PhpDocAttributeKey::PARENT);
            if ($parentType instanceof TypeNode) {
                // mirror attributes
                $newTypeNode->setAttribute(PhpDocAttributeKey::PARENT, $parentType);
            }

            return $newTypeNode;
        }

        return null;
    }

    /**
     * @param array<string, string> $oldToNewClasses
     */
    public function setOldToNewClasses(array $oldToNewClasses): void
    {
        $this->oldToNewClasses = $oldToNewClasses;
    }
}
