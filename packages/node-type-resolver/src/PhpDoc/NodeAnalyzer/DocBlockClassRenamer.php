<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Node as PhpDocParserNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\PHPStan\Type\ShortenedObjectType;
use Rector\SimplePhpDocParser\PhpDocNodeTraverser;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class DocBlockClassRenamer
{
    /**
     * @var bool
     */
    private $hasNodeChanged = false;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;

    public function __construct(PhpDocNodeTraverser $phpDocNodeTraverser, StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
    }

    /**
     * @param Type[] $oldTypes
     */
    public function renamePhpDocTypes(
        PhpDocNode $phpDocNode,
        array $oldTypes,
        Type $newType,
        Node $phpParserNode
    ): void {
        foreach ($oldTypes as $oldType) {
            $this->renamePhpDocType($phpDocNode, $oldType, $newType, $phpParserNode);
        }
    }

    public function renamePhpDocType(
        PhpDocNode $phpDocNode,
        Type $oldType,
        Type $newType,
        Node $phpParserNode
    ): bool {
        $this->phpDocNodeTraverser->traverseWithCallable(
            $phpDocNode,
            '',
            function (PhpDocParserNode $node) use ($phpParserNode, $oldType, $newType): PhpDocParserNode {
                if (! $node instanceof IdentifierTypeNode) {
                    return $node;
                }

                $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($node, $phpParserNode);

                // make sure to compare FQNs
                if ($staticType instanceof ShortenedObjectType) {
                    $staticType = new ObjectType($staticType->getFullyQualifiedName());
                }

                if (! $staticType->equals($oldType)) {
                    return $node;
                }

                $this->hasNodeChanged = true;

                return $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType);
            }
        );

        return $this->hasNodeChanged;
    }
}
