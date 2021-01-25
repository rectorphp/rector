<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Node as PhpDocParserNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

final class DocBlockClassRenamer
{
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
        PhpDocInfo $phpDocInfo,
        array $oldTypes,
        Type $newType,
        Node $phpParserNode
    ): void {
        foreach ($oldTypes as $oldType) {
            $this->renamePhpDocType($phpDocInfo, $oldType, $newType, $phpParserNode);
        }
    }

    public function renamePhpDocType(
        PhpDocInfo $phpDocInfo,
        Type $oldType,
        Type $newType,
        Node $phpParserNode
    ): void {
        $this->phpDocNodeTraverser->traverseWithCallable(
            $phpDocInfo->getPhpDocNode(),
            '',
            function (PhpDocParserNode $node) use ($phpDocInfo, $phpParserNode, $oldType, $newType): PhpDocParserNode {
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

                $phpDocInfo->markAsChanged();

                return $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType);
            }
        );
    }
}
