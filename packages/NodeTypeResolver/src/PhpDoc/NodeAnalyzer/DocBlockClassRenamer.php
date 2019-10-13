<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Node as PhpDocParserNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\Ast\PhpDocNodeTraverser;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\StaticTypeMapper;

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

    /**
     * @var bool
     */
    private $hasNodeChanged = false;

    public function __construct(StaticTypeMapper $staticTypeMapper, PhpDocNodeTraverser $phpDocNodeTraverser)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
    }

    public function renamePhpDocType(
        PhpDocNode $phpDocNode,
        Type $oldType,
        Type $newType,
        Node $phpParserNode
    ): bool {
        $this->phpDocNodeTraverser->traverseWithCallable(
            $phpDocNode,
            function (PhpDocParserNode $node) use ($phpParserNode, $oldType, $newType): PhpDocParserNode {
                if (! $node instanceof IdentifierTypeNode) {
                    return $node;
                }

                $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($node, $phpParserNode);
                if (! $staticType->equals($oldType)) {
                    return $node;
                }

                $newIdentifierType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType);
                if ($newIdentifierType === null) {
                    throw new ShouldNotHappenException();
                }

                $this->hasNodeChanged = true;

                return $newIdentifierType;
            }
        );

        return $this->hasNodeChanged;
    }
}
