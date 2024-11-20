<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Exception\NotImplementedYetException;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper;
use Rector\StaticTypeMapper\Naming\NameScopeFactory;
use Rector\StaticTypeMapper\PhpDoc\PhpDocTypeMapper;
/**
 * Maps PhpParser <=> PHPStan <=> PHPStan doc <=> string type nodes between all possible formats
 * @see \Rector\Tests\NodeTypeResolver\StaticTypeMapper\StaticTypeMapperTest
 */
final class StaticTypeMapper
{
    /**
     * @readonly
     */
    private NameScopeFactory $nameScopeFactory;
    /**
     * @readonly
     */
    private PHPStanStaticTypeMapper $phpStanStaticTypeMapper;
    /**
     * @readonly
     */
    private PhpDocTypeMapper $phpDocTypeMapper;
    /**
     * @readonly
     */
    private PhpParserNodeMapper $phpParserNodeMapper;
    public function __construct(NameScopeFactory $nameScopeFactory, PHPStanStaticTypeMapper $phpStanStaticTypeMapper, PhpDocTypeMapper $phpDocTypeMapper, PhpParserNodeMapper $phpParserNodeMapper)
    {
        $this->nameScopeFactory = $nameScopeFactory;
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
        $this->phpDocTypeMapper = $phpDocTypeMapper;
        $this->phpParserNodeMapper = $phpParserNodeMapper;
    }
    public function mapPHPStanTypeToPHPStanPhpDocTypeNode(Type $phpStanType) : TypeNode
    {
        return $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($phpStanType);
    }
    /**
     * @param TypeKind::* $typeKind
     * @return Name|ComplexType|Identifier|null
     */
    public function mapPHPStanTypeToPhpParserNode(Type $phpStanType, string $typeKind) : ?Node
    {
        return $this->phpStanStaticTypeMapper->mapToPhpParserNode($phpStanType, $typeKind);
    }
    public function mapPhpParserNodePHPStanType(Node $node) : Type
    {
        return $this->phpParserNodeMapper->mapToPHPStanType($node);
    }
    public function mapPHPStanPhpDocTypeToPHPStanType(PhpDocTagValueNode $phpDocTagValueNode, Node $node) : Type
    {
        if ($phpDocTagValueNode instanceof TemplateTagValueNode) {
            // special case
            if (!$phpDocTagValueNode->bound instanceof TypeNode) {
                return new MixedType();
            }
            $nameScope = $this->nameScopeFactory->createNameScopeFromNodeWithoutTemplateTypes($node);
            return $this->phpDocTypeMapper->mapToPHPStanType($phpDocTagValueNode->bound, $node, $nameScope);
        }
        if ($phpDocTagValueNode instanceof ReturnTagValueNode || $phpDocTagValueNode instanceof ParamTagValueNode || $phpDocTagValueNode instanceof VarTagValueNode || $phpDocTagValueNode instanceof ThrowsTagValueNode) {
            return $this->mapPHPStanPhpDocTypeNodeToPHPStanType($phpDocTagValueNode->type, $node);
        }
        throw new NotImplementedYetException(__METHOD__ . ' for ' . \get_class($phpDocTagValueNode));
    }
    public function mapPHPStanPhpDocTypeNodeToPHPStanType(TypeNode $typeNode, Node $node) : Type
    {
        $nameScope = $this->nameScopeFactory->createNameScopeFromNodeWithoutTemplateTypes($node);
        return $this->phpDocTypeMapper->mapToPHPStanType($typeNode, $node, $nameScope);
    }
}
