<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\PhpDocParser\Ast\Node as AstNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use Rector\Php\PhpVersionProvider;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper;
use Rector\ValueObject\PhpVersionFeature;
/**
 * @implements TypeMapperInterface<IntersectionType>
 */
final class IntersectionTypeMapper implements TypeMapperInterface
{
    /**
     * @readonly
     */
    private PhpVersionProvider $phpVersionProvider;
    /**
     * @readonly
     */
    private \Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectWithoutClassTypeMapper $objectWithoutClassTypeMapper;
    /**
     * @readonly
     */
    private \Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectTypeMapper $objectTypeMapper;
    /**
     * @readonly
     */
    private ScalarStringToTypeMapper $scalarStringToTypeMapper;
    public function __construct(PhpVersionProvider $phpVersionProvider, \Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectWithoutClassTypeMapper $objectWithoutClassTypeMapper, \Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectTypeMapper $objectTypeMapper, ScalarStringToTypeMapper $scalarStringToTypeMapper)
    {
        $this->phpVersionProvider = $phpVersionProvider;
        $this->objectWithoutClassTypeMapper = $objectWithoutClassTypeMapper;
        $this->objectTypeMapper = $objectTypeMapper;
        $this->scalarStringToTypeMapper = $scalarStringToTypeMapper;
    }
    public function getNodeClass() : string
    {
        return IntersectionType::class;
    }
    /**
     * @param IntersectionType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        $typeNode = $type->toPhpDocNode();
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($typeNode, '', function (AstNode $astNode) {
            if ($astNode instanceof UnionTypeNode) {
                return PhpDocNodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($astNode instanceof ArrayShapeItemNode) {
                return PhpDocNodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$astNode instanceof IdentifierTypeNode) {
                return PhpDocNodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            $type = $this->scalarStringToTypeMapper->mapScalarStringToType($astNode->name);
            if ($type->isScalar()->yes()) {
                return PhpDocNodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($type->isArray()->yes()) {
                return PhpDocNodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($type instanceof MixedType && $type->isExplicitMixed()) {
                return PhpDocNodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            $astNode->name = '\\' . \ltrim($astNode->name, '\\');
            return $astNode;
        });
        return $typeNode;
    }
    /**
     * @param IntersectionType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::INTERSECTION_TYPES)) {
            return null;
        }
        $intersectionedTypeNodes = [];
        foreach ($type->getTypes() as $type) {
            if ($type instanceof ObjectWithoutClassType) {
                return $this->objectWithoutClassTypeMapper->mapToPhpParserNode($type, $typeKind);
            }
            if (!$type instanceof ObjectType) {
                return null;
            }
            $resolvedType = $this->objectTypeMapper->mapToPhpParserNode($type, $typeKind);
            if (!$resolvedType instanceof FullyQualified) {
                return null;
            }
            $intersectionedTypeNodes[] = $resolvedType;
        }
        if ($intersectionedTypeNodes === []) {
            return null;
        }
        if (\count($intersectionedTypeNodes) === 1) {
            return \current($intersectionedTypeNodes);
        }
        if ($typeKind === TypeKind::UNION && !$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::UNION_INTERSECTION_TYPES)) {
            return null;
        }
        return new Node\IntersectionType($intersectionedTypeNodes);
    }
}
