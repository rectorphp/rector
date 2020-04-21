<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedException;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper;
use Rector\StaticTypeMapper\Mapper\StringTypeToPhpParserNodeMapper;
use Rector\StaticTypeMapper\PhpDoc\PhpDocTypeMapper;
use Rector\StaticTypeMapper\PHPStan\NameScopeFactory;

/**
 * Maps PhpParser <=> PHPStan <=> PHPStan doc <=> string type nodes between all possible formats
 */
final class StaticTypeMapper
{
    /**
     * @var PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;

    /**
     * @var PhpParserNodeMapper
     */
    private $phpParserNodeMapper;

    /**
     * @var PhpDocTypeMapper
     */
    private $phpDocTypeMapper;

    /**
     * @var StringTypeToPhpParserNodeMapper
     */
    private $stringTypeToPhpParserNodeMapper;

    /**
     * @var NameScopeFactory
     */
    private $nameScopeFactory;

    public function __construct(
        PHPStanStaticTypeMapper $phpStanStaticTypeMapper,
        PhpParserNodeMapper $phpParserNodeMapper,
        PhpDocTypeMapper $phpDocTypeMapper,
        StringTypeToPhpParserNodeMapper $stringTypeToPhpParserNodeMapper,
        NameScopeFactory $nameScopeFactory
    ) {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
        $this->phpParserNodeMapper = $phpParserNodeMapper;
        $this->phpDocTypeMapper = $phpDocTypeMapper;
        $this->stringTypeToPhpParserNodeMapper = $stringTypeToPhpParserNodeMapper;
        $this->nameScopeFactory = $nameScopeFactory;
    }

    public function mapPHPStanTypeToPHPStanPhpDocTypeNode(Type $phpStanType): TypeNode
    {
        return $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($phpStanType);
    }

    /**
     * @return Name|NullableType|PhpParserUnionType|null
     */
    public function mapPHPStanTypeToPhpParserNode(Type $phpStanType, ?string $kind = null): ?Node
    {
        return $this->phpStanStaticTypeMapper->mapToPhpParserNode($phpStanType, $kind);
    }

    public function mapPHPStanTypeToDocString(Type $phpStanType, ?Type $parentType = null): string
    {
        return $this->phpStanStaticTypeMapper->mapToDocString($phpStanType, $parentType);
    }

    public function mapPhpParserNodePHPStanType(Node $node): Type
    {
        return $this->phpParserNodeMapper->mapToPHPStanType($node);
    }

    public function mapPHPStanPhpDocTypeToPHPStanType(PhpDocTagValueNode $phpDocTagValueNode, Node $node): Type
    {
        if ($phpDocTagValueNode instanceof ReturnTagValueNode ||
            $phpDocTagValueNode instanceof ParamTagValueNode ||
            $phpDocTagValueNode instanceof VarTagValueNode ||
            $phpDocTagValueNode instanceof ThrowsTagValueNode
        ) {
            return $this->mapPHPStanPhpDocTypeNodeToPHPStanType($phpDocTagValueNode->type, $node);
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($phpDocTagValueNode));
    }

    /**
     * @return Identifier|Name|NullableType
     */
    public function mapStringToPhpParserNode(string $type): Node
    {
        return $this->stringTypeToPhpParserNodeMapper->map($type);
    }

    public function mapPHPStanPhpDocTypeNodeToPhpDocString(TypeNode $typeNode, Node $node): string
    {
        $phpStanType = $this->mapPHPStanPhpDocTypeNodeToPHPStanType($typeNode, $node);

        return $this->mapPHPStanTypeToDocString($phpStanType);
    }

    public function mapPHPStanPhpDocTypeNodeToPHPStanType(TypeNode $typeNode, Node $node): Type
    {
        $nameScope = $this->nameScopeFactory->createNameScopeFromNode($node);

        return $this->phpDocTypeMapper->mapToPHPStanType($typeNode, $node, $nameScope);
    }
}
