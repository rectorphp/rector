<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\TypeMapper;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\ComplexType;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\NullableType;
use RectorPrefix20220606\PhpParser\Node\UnionType as PhpParserUnionType;
use RectorPrefix20220606\PhpParser\NodeAbstract;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use RectorPrefix20220606\PHPStan\Type\ClassStringType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantBooleanType;
use RectorPrefix20220606\PHPStan\Type\Generic\GenericClassStringType;
use RectorPrefix20220606\PHPStan\Type\IterableType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\NullType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\TypeWithClassName;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\PHPStan\Type\VoidType;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use RectorPrefix20220606\Rector\Core\Enum\ObjectReference;
use RectorPrefix20220606\Rector\Core\Php\PhpVersionProvider;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersionFeature;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\TypeAnalyzer\BoolUnionTypeAnalyzer;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeCommonTypeNarrower;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\ValueObject\UnionTypeAnalysis;
use RectorPrefix20220606\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements TypeMapperInterface<UnionType>
 */
final class UnionTypeMapper implements TypeMapperInterface
{
    /**
     * @var \Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer
     */
    private $doctrineTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer
     */
    private $unionTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\BoolUnionTypeAnalyzer
     */
    private $boolUnionTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeCommonTypeNarrower
     */
    private $unionTypeCommonTypeNarrower;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(DoctrineTypeAnalyzer $doctrineTypeAnalyzer, PhpVersionProvider $phpVersionProvider, UnionTypeAnalyzer $unionTypeAnalyzer, BoolUnionTypeAnalyzer $boolUnionTypeAnalyzer, UnionTypeCommonTypeNarrower $unionTypeCommonTypeNarrower, NodeNameResolver $nodeNameResolver)
    {
        $this->doctrineTypeAnalyzer = $doctrineTypeAnalyzer;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->unionTypeAnalyzer = $unionTypeAnalyzer;
        $this->boolUnionTypeAnalyzer = $boolUnionTypeAnalyzer;
        $this->unionTypeCommonTypeNarrower = $unionTypeCommonTypeNarrower;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @required
     */
    public function autowire(PHPStanStaticTypeMapper $phpStanStaticTypeMapper) : void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return UnionType::class;
    }
    /**
     * @param UnionType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, string $typeKind) : TypeNode
    {
        $unionTypesNodes = [];
        $skipIterable = $this->shouldSkipIterable($type);
        foreach ($type->getTypes() as $unionedType) {
            if ($unionedType instanceof IterableType && $skipIterable) {
                continue;
            }
            $unionTypesNodes[] = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($unionedType, $typeKind);
        }
        $unionTypesNodes = \array_unique($unionTypesNodes);
        return new BracketsAwareUnionTypeNode($unionTypesNodes);
    }
    /**
     * @param UnionType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        $arrayNode = $this->matchArrayTypes($type);
        if ($arrayNode !== null) {
            return $arrayNode;
        }
        if ($this->boolUnionTypeAnalyzer->isNullableBoolUnionType($type) && !$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::UNION_TYPES)) {
            return new NullableType(new Name('bool'));
        }
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::UNION_TYPES) && $this->isFalseBoolUnion($type)) {
            // return new Bool
            return new Name('bool');
        }
        // special case for nullable
        $nullabledType = $this->matchTypeForNullableUnionType($type);
        if (!$nullabledType instanceof Type) {
            // use first unioned type in case of unioned object types
            return $this->matchTypeForUnionedObjectTypes($type, $typeKind);
        }
        // void cannot be nullable
        if ($nullabledType instanceof VoidType) {
            return null;
        }
        $nullabledTypeNode = $this->phpStanStaticTypeMapper->mapToPhpParserNode($nullabledType, $typeKind);
        if (!$nullabledTypeNode instanceof Node) {
            return null;
        }
        if (\in_array(\get_class($nullabledTypeNode), [NullableType::class, ComplexType::class], \true)) {
            return $nullabledTypeNode;
        }
        /** @var Name $nullabledTypeNode */
        if (!$this->nodeNameResolver->isName($nullabledTypeNode, 'false')) {
            return new NullableType($nullabledTypeNode);
        }
        return null;
    }
    private function shouldSkipIterable(UnionType $unionType) : bool
    {
        $unionTypeAnalysis = $this->unionTypeAnalyzer->analyseForNullableAndIterable($unionType);
        if (!$unionTypeAnalysis instanceof UnionTypeAnalysis) {
            return \false;
        }
        if (!$unionTypeAnalysis->hasIterable()) {
            return \false;
        }
        return $unionTypeAnalysis->hasArray();
    }
    /**
     * @return \PhpParser\Node\Name|\PhpParser\Node\NullableType|null
     */
    private function matchArrayTypes(UnionType $unionType)
    {
        $unionTypeAnalysis = $this->unionTypeAnalyzer->analyseForNullableAndIterable($unionType);
        if (!$unionTypeAnalysis instanceof UnionTypeAnalysis) {
            return null;
        }
        $type = $unionTypeAnalysis->hasIterable() ? 'iterable' : 'array';
        if ($unionTypeAnalysis->isNullableType()) {
            return new NullableType($type);
        }
        return new Name($type);
    }
    private function matchTypeForNullableUnionType(UnionType $unionType) : ?Type
    {
        if (\count($unionType->getTypes()) !== 2) {
            return null;
        }
        $firstType = $unionType->getTypes()[0];
        $secondType = $unionType->getTypes()[1];
        if ($firstType instanceof NullType) {
            return $secondType;
        }
        if ($secondType instanceof NullType) {
            return $firstType;
        }
        return null;
    }
    private function hasObjectAndStaticType(PhpParserUnionType $phpParserUnionType) : bool
    {
        $typeNames = $this->nodeNameResolver->getNames($phpParserUnionType->types);
        $diff = \array_diff(['object', ObjectReference::STATIC], $typeNames);
        return $diff === [];
    }
    /**
     * @param TypeKind::* $typeKind
     * @return Name|FullyQualified|PhpParserUnionType|NullableType|null
     */
    private function matchTypeForUnionedObjectTypes(UnionType $unionType, string $typeKind) : ?Node
    {
        $phpParserUnionType = $this->matchPhpParserUnionType($unionType, $typeKind);
        if ($phpParserUnionType !== null) {
            if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::UNION_TYPES)) {
                // maybe all one type?
                if ($this->boolUnionTypeAnalyzer->isBoolUnionType($unionType)) {
                    return new Name('bool');
                }
                return null;
            }
            if ($this->hasObjectAndStaticType($phpParserUnionType)) {
                return null;
            }
            return $phpParserUnionType;
        }
        if ($this->boolUnionTypeAnalyzer->isBoolUnionType($unionType)) {
            return new Name('bool');
        }
        $compatibleObjectType = $this->processResolveCompatibleObjectCandidates($unionType);
        if ($compatibleObjectType instanceof NullableType || $compatibleObjectType instanceof FullyQualified) {
            return $compatibleObjectType;
        }
        return $this->processResolveCompatibleStringCandidates($unionType);
    }
    private function processResolveCompatibleStringCandidates(UnionType $unionType) : ?Name
    {
        foreach ($unionType->getTypes() as $type) {
            if (!\in_array(\get_class($type), [ClassStringType::class, GenericClassStringType::class], \true)) {
                return null;
            }
        }
        return new Name('string');
    }
    private function processResolveCompatibleObjectCandidates(UnionType $unionType) : ?Node
    {
        // the type should be compatible with all other types, e.g. A extends B, B
        $compatibleObjectType = $this->resolveCompatibleObjectCandidate($unionType);
        if ($compatibleObjectType instanceof UnionType) {
            $type = $this->matchTypeForNullableUnionType($compatibleObjectType);
            if ($type instanceof ObjectType) {
                return new NullableType(new FullyQualified($type->getClassName()));
            }
        }
        if (!$compatibleObjectType instanceof ObjectType) {
            return null;
        }
        return new FullyQualified($compatibleObjectType->getClassName());
    }
    /**
     * @param TypeKind::* $typeKind
     */
    private function matchPhpParserUnionType(UnionType $unionType, string $typeKind) : ?PhpParserUnionType
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::UNION_TYPES)) {
            return null;
        }
        $phpParserUnionedTypes = [];
        foreach ($unionType->getTypes() as $unionedType) {
            // void type and mixed type are not allowed in union
            if (\in_array(\get_class($unionedType), [MixedType::class, VoidType::class], \true)) {
                return null;
            }
            /**
             * NullType inside UnionType is allowed
             * make it on TypeKind property as changing other type, eg: return type may conflict with parent child implementation
             *
             * @var Identifier|Name|null $phpParserNode
             */
            $phpParserNode = $unionedType instanceof NullType && $typeKind === TypeKind::PROPERTY ? new Name('null') : $this->phpStanStaticTypeMapper->mapToPhpParserNode($unionedType, $typeKind);
            if ($phpParserNode === null) {
                return null;
            }
            $phpParserUnionedTypes[] = $phpParserNode;
        }
        $phpParserUnionedTypes = \array_unique($phpParserUnionedTypes);
        if (\count($phpParserUnionedTypes) < 2) {
            return null;
        }
        return new PhpParserUnionType($phpParserUnionedTypes);
    }
    /**
     * @return \PHPStan\Type\UnionType|\PHPStan\Type\TypeWithClassName|null
     */
    private function resolveCompatibleObjectCandidate(UnionType $unionType)
    {
        if ($this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($unionType)) {
            $objectType = new ObjectType('Doctrine\\Common\\Collections\\Collection');
            return $this->unionTypeAnalyzer->isNullable($unionType) ? new UnionType([new NullType(), $objectType]) : $objectType;
        }
        $typesWithClassNames = $this->unionTypeAnalyzer->matchExclusiveTypesWithClassNames($unionType);
        if ($typesWithClassNames === []) {
            return null;
        }
        $sharedTypeWithClassName = $this->matchTwoObjectTypes($typesWithClassNames);
        if ($sharedTypeWithClassName instanceof TypeWithClassName) {
            return $this->correctObjectType($sharedTypeWithClassName);
        }
        // find least common denominator
        return $this->unionTypeCommonTypeNarrower->narrowToSharedObjectType($unionType);
    }
    /**
     * @param TypeWithClassName[] $typesWithClassNames
     */
    private function matchTwoObjectTypes(array $typesWithClassNames) : ?TypeWithClassName
    {
        foreach ($typesWithClassNames as $typeWithClassName) {
            foreach ($typesWithClassNames as $nestedTypeWithClassName) {
                if (!$this->areTypeWithClassNamesRelated($typeWithClassName, $nestedTypeWithClassName)) {
                    continue 2;
                }
            }
            return $typeWithClassName;
        }
        return null;
    }
    private function areTypeWithClassNamesRelated(TypeWithClassName $firstType, TypeWithClassName $secondType) : bool
    {
        return $firstType->accepts($secondType, \false)->yes();
    }
    private function correctObjectType(TypeWithClassName $typeWithClassName) : TypeWithClassName
    {
        if ($typeWithClassName->getClassName() === NodeAbstract::class) {
            return new ObjectType('PhpParser\\Node');
        }
        if ($typeWithClassName->getClassName() === AbstractRector::class) {
            return new ObjectType('Rector\\Core\\Contract\\Rector\\RectorInterface');
        }
        return $typeWithClassName;
    }
    private function isFalseBoolUnion(UnionType $unionType) : bool
    {
        if (\count($unionType->getTypes()) !== 2) {
            return \false;
        }
        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof ConstantBooleanType) {
                continue;
            }
            return \false;
        }
        return \true;
    }
}
