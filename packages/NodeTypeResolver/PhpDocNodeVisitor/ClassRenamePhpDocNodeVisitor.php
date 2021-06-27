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
use Rector\NodeTypeResolver\ValueObject\OldToNewType;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;

final class ClassRenamePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    /**
     * @var OldToNewType[]
     */
    private array $oldToNewTypes = [];

    public function __construct(
        private StaticTypeMapper $staticTypeMapper,
        private CurrentNodeProvider $currentNodeProvider
    ) {
    }

    public function beforeTraverse(Node $node): void
    {
        if ($this->oldToNewTypes === []) {
            throw new ShouldNotHappenException('Configure "$oldToNewClasses" first');
        }
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof IdentifierTypeNode) {
            return null;
        }

        $phpParserNode = $this->currentNodeProvider->getNode();
        if (! $phpParserNode instanceof \PhpParser\Node) {
            throw new ShouldNotHappenException();
        }

        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($node, $phpParserNode);

        // make sure to compare FQNs
        if ($staticType instanceof ShortenedObjectType) {
            $staticType = new ObjectType($staticType->getFullyQualifiedName());
        }

        foreach ($this->oldToNewTypes as $oldToNewType) {
            if (! $staticType->equals($oldToNewType->getOldType())) {
                continue;
            }

            $newTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($oldToNewType->getNewType());

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
     * @param OldToNewType[] $oldToNewTypes
     */
    public function setOldToNewTypes(array $oldToNewTypes): void
    {
        $this->oldToNewTypes = $oldToNewTypes;
    }
}
