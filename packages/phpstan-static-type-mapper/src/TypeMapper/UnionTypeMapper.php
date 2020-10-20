<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\IterableType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareUnionTypeNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer;

final class UnionTypeMapper implements TypeMapperInterface
{
    /**
     * @var PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;

    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    /**
     * @var UnionTypeAnalyzer
     */
    private $unionTypeAnalyzer;

    /**
     * @var DoctrineTypeAnalyzer
     */
    private $doctrineTypeAnalyzer;

    public function __construct(
        DoctrineTypeAnalyzer $doctrineTypeAnalyzer,
        PhpVersionProvider $phpVersionProvider,
        UnionTypeAnalyzer $unionTypeAnalyzer
    ) {
        $this->phpVersionProvider = $phpVersionProvider;
        $this->unionTypeAnalyzer = $unionTypeAnalyzer;
        $this->doctrineTypeAnalyzer = $doctrineTypeAnalyzer;
    }

    /**
     * @required
     */
    public function autowireUnionTypeMapper(PHPStanStaticTypeMapper $phpStanStaticTypeMapper): void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }

    public function getNodeClass(): string
    {
        return UnionType::class;
    }

    /**
     * @param UnionType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        $unionTypesNodes = [];
        $skipIterable = $this->shouldSkipIterable($type);

        foreach ($type->getTypes() as $unionedType) {
            if ($unionedType instanceof IterableType && $skipIterable) {
                continue;
            }

            $unionTypesNodes[] = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($unionedType);
        }

        $unionTypesNodes = array_unique($unionTypesNodes);

        return new AttributeAwareUnionTypeNode($unionTypesNodes);
    }

    /**
     * @param UnionType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        // match array types
        $arrayNode = $this->matchArrayTypes($type);
        if ($arrayNode !== null) {
            return $arrayNode;
        }

        // special case for nullable
        $nullabledType = $this->matchTypeForNullableUnionType($type);
        if ($nullabledType === null) {
            // use first unioned type in case of unioned object types
            return $this->matchTypeForUnionedObjectTypes($type);
        }

        // void cannot be nullable
        if ($nullabledType instanceof VoidType) {
            return null;
        }

        $nullabledTypeNode = $this->phpStanStaticTypeMapper->mapToPhpParserNode($nullabledType);
        if ($nullabledTypeNode === null) {
            return null;
        }

        if ($nullabledTypeNode instanceof NullableType) {
            return $nullabledTypeNode;
        }

        if ($nullabledTypeNode instanceof PhpParserUnionType) {
            throw new ShouldNotHappenException();
        }

        return new NullableType($nullabledTypeNode);
    }

    /**
     * @param UnionType $type
     */
    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        $docStrings = [];

        foreach ($type->getTypes() as $unionedType) {
            $docStrings[] = $this->phpStanStaticTypeMapper->mapToDocString($unionedType);
        }

        // remove empty values, e.g. void/iterable
        $docStrings = array_unique($docStrings);
        $docStrings = array_filter($docStrings);

        return implode('|', $docStrings);
    }

    private function shouldSkipIterable(UnionType $unionType): bool
    {
        $unionTypeAnalysis = $this->unionTypeAnalyzer->analyseForNullableAndIterable($unionType);
        if ($unionTypeAnalysis === null) {
            return false;
        }

        return $unionTypeAnalysis->hasIterable() && $unionTypeAnalysis->hasArray();
    }

    /**
     * @return Name|NullableType|null
     */
    private function matchArrayTypes(UnionType $unionType): ?Node
    {
        $unionTypeAnalysis = $this->unionTypeAnalyzer->analyseForNullableAndIterable($unionType);
        if ($unionTypeAnalysis === null) {
            return null;
        }

        $type = $unionTypeAnalysis->hasIterable() ? 'iterable' : 'array';
        if ($unionTypeAnalysis->isNullableType()) {
            return new NullableType($type);
        }

        return new Name($type);
    }

    private function matchTypeForNullableUnionType(UnionType $unionType): ?Type
    {
        if (count($unionType->getTypes()) !== 2) {
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

    /**
     * @return Name|FullyQualified|PhpParserUnionType|null
     */
    private function matchTypeForUnionedObjectTypes(UnionType $unionType): ?Node
    {
        $phpParserUnionType = $this->matchPhpParserUnionType($unionType);
        if ($phpParserUnionType !== null) {
            if (! $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::UNION_TYPES)) {
                return null;
            }

            return $phpParserUnionType;
        }

        // the type should be compatible with all other types, e.g. A extends B, B
        $compatibleObjectCandidate = $this->resolveCompatibleObjectCandidate($unionType);

        if ($compatibleObjectCandidate === null) {
            return null;
        }

        return new FullyQualified($compatibleObjectCandidate);
    }

    private function matchPhpParserUnionType(UnionType $unionType): ?PhpParserUnionType
    {
        if (! $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::UNION_TYPES)) {
            return null;
        }

        $phpParserUnionedTypes = [];

        foreach ($unionType->getTypes() as $unionedType) {
            /** @var Identifier|Name|null $phpParserNode */
            $phpParserNode = $this->phpStanStaticTypeMapper->mapToPhpParserNode($unionedType);
            if ($phpParserNode === null) {
                return null;
            }

            $phpParserUnionedTypes[] = $phpParserNode;
        }

        return new PhpParserUnionType($phpParserUnionedTypes);
    }

    private function resolveCompatibleObjectCandidate(UnionType $unionType): ?string
    {
        if ($this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($unionType)) {
            return 'Doctrine\Common\Collections\Collection';
        }

        if (! $this->isUnionTypeWithTypeClassNameOnly($unionType)) {
            return null;
        }

        /** @var TypeWithClassName $unionedType */
        foreach ($unionType->getTypes() as $unionedType) {
            /** @var TypeWithClassName $nestedUnionedType */
            foreach ($unionType->getTypes() as $nestedUnionedType) {
                if (! $this->areTypeWithClassNamesRelated($unionedType, $nestedUnionedType)) {
                    continue 2;
                }
            }

            return $unionedType->getClassName();
        }

        return null;
    }

    private function isUnionTypeWithTypeClassNameOnly(UnionType $unionType): bool
    {
        foreach ($unionType->getTypes() as $unionedType) {
            if (! $unionedType instanceof TypeWithClassName) {
                return false;
            }
        }

        return true;
    }

    private function areTypeWithClassNamesRelated(TypeWithClassName $firstType, TypeWithClassName $secondType): bool
    {
        if (is_a($firstType->getClassName(), $secondType->getClassName(), true)) {
            return true;
        }

        return is_a($secondType->getClassName(), $firstType->getClassName(), true);
    }
}
