<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\StaticTypeMapper;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\ComplexType;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\UnionType;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\Core\Exception\NotImplementedYetException;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use RectorPrefix20220606\Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper;
use RectorPrefix20220606\Rector\StaticTypeMapper\Naming\NameScopeFactory;
use RectorPrefix20220606\Rector\StaticTypeMapper\PhpDoc\PhpDocTypeMapper;
/**
 * Maps PhpParser <=> PHPStan <=> PHPStan doc <=> string type nodes between all possible formats
 * @see \Rector\Tests\NodeTypeResolver\StaticTypeMapper\StaticTypeMapperTest
 */
final class StaticTypeMapper
{
    /**
     * @var array<string, string>
     */
    private const STANDALONE_MAPS = ['false' => 'bool'];
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\Naming\NameScopeFactory
     */
    private $nameScopeFactory;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\PhpDoc\PhpDocTypeMapper
     */
    private $phpDocTypeMapper;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper
     */
    private $phpParserNodeMapper;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NameScopeFactory $nameScopeFactory, PHPStanStaticTypeMapper $phpStanStaticTypeMapper, PhpDocTypeMapper $phpDocTypeMapper, PhpParserNodeMapper $phpParserNodeMapper, NodeNameResolver $nodeNameResolver)
    {
        $this->nameScopeFactory = $nameScopeFactory;
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
        $this->phpDocTypeMapper = $phpDocTypeMapper;
        $this->phpParserNodeMapper = $phpParserNodeMapper;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param TypeKind::* $typeKind
     */
    public function mapPHPStanTypeToPHPStanPhpDocTypeNode(Type $phpStanType, string $typeKind) : TypeNode
    {
        return $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($phpStanType, $typeKind);
    }
    /**
     * @param TypeKind::* $typeKind
     * @return Name|ComplexType|null
     */
    public function mapPHPStanTypeToPhpParserNode(Type $phpStanType, string $typeKind) : ?Node
    {
        $node = $this->phpStanStaticTypeMapper->mapToPhpParserNode($phpStanType, $typeKind);
        if (!$node instanceof Node) {
            return null;
        }
        if ($node instanceof UnionType) {
            return $node;
        }
        if (!$node instanceof Name) {
            return $node;
        }
        $nodeName = $this->nodeNameResolver->getName($node);
        foreach (self::STANDALONE_MAPS as $key => $type) {
            if ($nodeName === $key) {
                return new Name($type);
            }
        }
        return $node;
    }
    public function mapPhpParserNodePHPStanType(Node $node) : Type
    {
        return $this->phpParserNodeMapper->mapToPHPStanType($node);
    }
    public function mapPHPStanPhpDocTypeToPHPStanType(PhpDocTagValueNode $phpDocTagValueNode, Node $node) : Type
    {
        if ($phpDocTagValueNode instanceof TemplateTagValueNode) {
            // special case
            $nameScope = $this->nameScopeFactory->createNameScopeFromNodeWithoutTemplateTypes($node);
            if ($phpDocTagValueNode->bound === null) {
                return new MixedType();
            }
            return $this->phpDocTypeMapper->mapToPHPStanType($phpDocTagValueNode->bound, $node, $nameScope);
        }
        if ($phpDocTagValueNode instanceof ReturnTagValueNode || $phpDocTagValueNode instanceof ParamTagValueNode || $phpDocTagValueNode instanceof VarTagValueNode || $phpDocTagValueNode instanceof ThrowsTagValueNode) {
            return $this->mapPHPStanPhpDocTypeNodeToPHPStanType($phpDocTagValueNode->type, $node);
        }
        throw new NotImplementedYetException(__METHOD__ . ' for ' . \get_class($phpDocTagValueNode));
    }
    public function mapPHPStanPhpDocTypeNodeToPHPStanType(TypeNode $typeNode, Node $node) : Type
    {
        if ($node instanceof Param) {
            $classMethod = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($classMethod instanceof ClassMethod) {
                // param does not hany any clue about template map, but class method has
                $node = $classMethod;
            }
        }
        $nameScope = $this->nameScopeFactory->createNameScopeFromNode($node);
        return $this->phpDocTypeMapper->mapToPHPStanType($typeNode, $node, $nameScope);
    }
}
