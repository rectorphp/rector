<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Php\PhpVersion;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NativeTypePosition;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\UnionType;
use Rector\Exception\NotImplementedYetException;
use Rector\Php\PhpVersionProvider;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
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
    private PhpDocTypeMapper $phpDocTypeMapper;
    /**
     * @readonly
     */
    private PhpParserNodeMapper $phpParserNodeMapper;
    /**
     * @readonly
     */
    private PhpVersionProvider $phpVersionProvider;
    public function __construct(NameScopeFactory $nameScopeFactory, PhpDocTypeMapper $phpDocTypeMapper, PhpParserNodeMapper $phpParserNodeMapper, PhpVersionProvider $phpVersionProvider)
    {
        $this->nameScopeFactory = $nameScopeFactory;
        $this->phpDocTypeMapper = $phpDocTypeMapper;
        $this->phpParserNodeMapper = $phpParserNodeMapper;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function mapPHPStanTypeToPHPStanPhpDocTypeNode(Type $phpStanType): TypeNode
    {
        // explicit int values and ranges are too detailed for a docblock
        $phpStanType = TypeTraverser::map($phpStanType, static function (Type $type, callable $traverse): Type {
            if ($type instanceof IntegerType) {
                return new IntegerType();
            }
            if ($type instanceof UnionType || $type instanceof ArrayType) {
                return $traverse($type);
            }
            return $type;
        });
        return $this->styleTypeNode($phpStanType->toPhpDocNode());
    }
    /**
     * @param TypeKind::* $typeKind
     * @return \PhpParser\Node\ComplexType|\PhpParser\Node\Identifier|\PhpParser\Node\Name|null
     */
    public function mapPHPStanTypeToPhpParserNode(Type $phpStanType, string $typeKind)
    {
        if ($typeKind === TypeKind::PARAM) {
            return $phpStanType->toNativePhpTypeNode(NativeTypePosition::parameter($this->getPhpVersion()));
        }
        if ($typeKind === TypeKind::RETURN) {
            return $phpStanType->toNativePhpTypeNode(NativeTypePosition::returnType($this->getPhpVersion()));
        }
        // Rector policy: a property or constant holding only true, false or null is too narrow to be useful
        if ($phpStanType->isNull()->yes()) {
            return null;
        }
        $phpStanType = TypeTraverser::map($phpStanType, static function (Type $type, callable $traverse): Type {
            if ($type->isTrue()->yes() || $type->isFalse()->yes()) {
                return new BooleanType();
            }
            return $traverse($type);
        });
        return $phpStanType->toNativePhpTypeNode(NativeTypePosition::property($this->getPhpVersion()));
    }
    public function mapPhpParserNodePHPStanType(Node $node): Type
    {
        return $this->phpParserNodeMapper->mapToPHPStanType($node);
    }
    public function mapPHPStanPhpDocTypeToPHPStanType(PhpDocTagValueNode $phpDocTagValueNode, Node $node): Type
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
        throw new NotImplementedYetException(__METHOD__ . ' for ' . get_class($phpDocTagValueNode));
    }
    public function mapPHPStanPhpDocTypeNodeToPHPStanType(TypeNode $typeNode, Node $node): Type
    {
        $nameScope = $this->nameScopeFactory->createNameScopeFromNodeWithoutTemplateTypes($node);
        return $this->phpDocTypeMapper->mapToPHPStanType($typeNode, $node, $nameScope);
    }
    /**
     * Rector prints "A[]" instead of "array<A>" or "array<int, A>"
     */
    private function styleTypeNode(TypeNode $typeNode): TypeNode
    {
        if ($typeNode instanceof UnionTypeNode) {
            $unionedTypeNodes = [];
            foreach ($typeNode->types as $unionedTypeNode) {
                $unionedTypeNode = $this->styleTypeNode($unionedTypeNode);
                // "A[]|B[]" from an array of a union is merged into this union
                foreach ($unionedTypeNode instanceof UnionTypeNode ? $unionedTypeNode->types : [$unionedTypeNode] as $singleTypeNode) {
                    $unionedTypeNodes[(string) $singleTypeNode] = $singleTypeNode;
                }
            }
            return new UnionTypeNode(array_values($unionedTypeNodes));
        }
        if ($typeNode instanceof IntersectionTypeNode) {
            return new IntersectionTypeNode(array_map(\Closure::fromCallable([$this, 'styleTypeNode']), $typeNode->types));
        }
        if ($typeNode instanceof IdentifierTypeNode && $typeNode->name === 'array') {
            return new ArrayTypeNode(new IdentifierTypeNode('mixed'));
        }
        if (!$typeNode instanceof GenericTypeNode) {
            return $typeNode;
        }
        $genericTypeNodes = array_map(\Closure::fromCallable([$this, 'styleTypeNode']), $typeNode->genericTypes);
        if ($typeNode->type instanceof IdentifierTypeNode && $typeNode->type->name === 'array' && $this->isArrayOfItemsOnly($genericTypeNodes)) {
            $itemTypeNode = $genericTypeNodes[array_key_last($genericTypeNodes)];
            if (!$itemTypeNode instanceof UnionTypeNode) {
                return new ArrayTypeNode($itemTypeNode);
            }
            return new UnionTypeNode(array_map(static fn(TypeNode $unionedTypeNode): ArrayTypeNode => new ArrayTypeNode($unionedTypeNode), $itemTypeNode->types));
        }
        $typeNode->genericTypes = $genericTypeNodes;
        return $typeNode;
    }
    /**
     * "array<A>" or "array<int, A>" can be printed as "A[]", unless that would lose the int key of nested arrays,
     * or turn "array<A<X>|A<Y>>" into "A<X>[]|A<Y>[]"
     *
     * @param TypeNode[] $genericTypeNodes
     */
    private function isArrayOfItemsOnly(array $genericTypeNodes): bool
    {
        if (count($genericTypeNodes) === 2 && (string) $genericTypeNodes[0] !== 'int') {
            return \false;
        }
        $itemTypeNode = $genericTypeNodes[array_key_last($genericTypeNodes)];
        $itemTypeNodes = $itemTypeNode instanceof UnionTypeNode ? $itemTypeNode->types : [$itemTypeNode];
        foreach ($itemTypeNodes as $singleItemTypeNode) {
            if ($singleItemTypeNode instanceof GenericTypeNode && count($itemTypeNodes) > 1) {
                return \false;
            }
            if (count($genericTypeNodes) === 2 && ($singleItemTypeNode instanceof ArrayTypeNode || $singleItemTypeNode instanceof GenericTypeNode)) {
                return \false;
            }
        }
        return \true;
    }
    private function getPhpVersion(): PhpVersion
    {
        return new PhpVersion($this->phpVersionProvider->provide());
    }
}
