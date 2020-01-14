<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareUnionTypeNode;
use Rector\Exception\NotImplementedException;
use Rector\Exception\ShouldNotHappenException;
use Rector\Php\PhpVersionProvider;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\ParentStaticType;
use Rector\PHPStan\Type\SelfObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;
use Rector\ValueObject\PhpVersionFeature;
use Traversable;

final class PHPStanStaticTypeMapper
{
    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }

    public function mapToPHPStanPhpDocTypeNode(Type $phpStanType)
    {
        if ($phpStanType instanceof MixedType) {
            return new IdentifierTypeNode('mixed');
        }

        if ($phpStanType instanceof UnionType) {
            $unionTypesNodes = [];
            foreach ($phpStanType->getTypes() as $unionedType) {
                $unionTypesNodes[] = $this->mapToPHPStanPhpDocTypeNode($unionedType);
            }

            $unionTypesNodes = array_unique($unionTypesNodes);

            return new AttributeAwareUnionTypeNode($unionTypesNodes);
        }

        if ($phpStanType instanceof ArrayType || $phpStanType instanceof IterableType) {
            $itemTypeNode = $this->mapToPHPStanPhpDocTypeNode($phpStanType->getItemType());

            if ($itemTypeNode instanceof UnionTypeNode) {
                return $this->convertUnionArrayTypeNodesToArrayTypeOfUnionTypeNodes($itemTypeNode);
            }

            return new ArrayTypeNode($itemTypeNode);
        }

        if ($phpStanType instanceof IntegerType) {
            return new IdentifierTypeNode('int');
        }

        if ($phpStanType instanceof ClassStringType) {
            return new IdentifierTypeNode('class-string');
        }

        if ($phpStanType instanceof StringType) {
            return new IdentifierTypeNode('string');
        }

        if ($phpStanType instanceof BooleanType) {
            return new IdentifierTypeNode('bool');
        }

        if ($phpStanType instanceof FloatType) {
            return new IdentifierTypeNode('float');
        }

        if ($phpStanType instanceof ObjectType) {
            return new IdentifierTypeNode('\\' . $phpStanType->getClassName());
        }

        if ($phpStanType instanceof NullType) {
            return new IdentifierTypeNode('null');
        }

        if ($phpStanType instanceof NeverType) {
            return new IdentifierTypeNode('mixed');
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($phpStanType));
    }

    /**
     * @return Identifier|Name|NullableType|PhpParserUnionType|null
     */
    public function mapToPhpParserNode(Type $phpStanType, ?string $kind = null): ?Node
    {
        if ($phpStanType instanceof VoidType) {
            if ($this->phpVersionProvider->isAtLeast(PhpVersionFeature::VOID_TYPE)) {
                if (in_array($kind, ['param', 'property'], true)) {
                    // param cannot be void
                    return null;
                }

                return new Identifier('void');
            }

            return null;
        }

        if ($phpStanType instanceof SelfObjectType) {
            return new Identifier('self');
        }

        if ($phpStanType instanceof IntegerType) {
            if ($this->phpVersionProvider->isAtLeast(PhpVersionFeature::SCALAR_TYPES)) {
                return new Identifier('int');
            }

            return null;
        }

        if ($phpStanType instanceof StringType) {
            if ($this->phpVersionProvider->isAtLeast(PhpVersionFeature::SCALAR_TYPES)) {
                return new Identifier('string');
            }

            return null;
        }

        if ($phpStanType instanceof BooleanType) {
            if ($this->phpVersionProvider->isAtLeast(PhpVersionFeature::SCALAR_TYPES)) {
                return new Identifier('bool');
            }

            return null;
        }

        if ($phpStanType instanceof FloatType) {
            if ($this->phpVersionProvider->isAtLeast(PhpVersionFeature::SCALAR_TYPES)) {
                return new Identifier('float');
            }

            return null;
        }

        if ($phpStanType instanceof ArrayType) {
            return new Identifier('array');
        }

        if ($phpStanType instanceof IterableType) {
            return new Identifier('iterable');
        }

        if ($phpStanType instanceof ThisType) {
            return new Identifier('self');
        }

        if ($phpStanType instanceof ParentStaticType) {
            return new Identifier('parent');
        }

        if ($phpStanType instanceof StaticType) {
            return null;
        }

        if ($phpStanType instanceof CallableType || $phpStanType instanceof ClosureType) {
            if ($kind === 'property') {
                return null;
            }

            return new Identifier('callable');
        }

        if ($phpStanType instanceof ShortenedObjectType) {
            return new Name\FullyQualified($phpStanType->getFullyQualifiedName());
        }

        if ($phpStanType instanceof AliasedObjectType) {
            return new Name($phpStanType->getClassName());
        }

        if ($phpStanType instanceof TypeWithClassName) {
            $lowerCasedClassName = strtolower($phpStanType->getClassName());
            if ($lowerCasedClassName === 'callable') {
                return new Identifier('callable');
            }

            if ($lowerCasedClassName === 'self') {
                return new Identifier('self');
            }

            if ($lowerCasedClassName === 'static') {
                return null;
            }

            if ($lowerCasedClassName === 'mixed') {
                return null;
            }

            return new Name\FullyQualified($phpStanType->getClassName());
        }

        if ($phpStanType instanceof UnionType) {
            // match array types
            $arrayNode = $this->matchArrayTypes($phpStanType);
            if ($arrayNode !== null) {
                return $arrayNode;
            }

            // special case for nullable
            $nullabledType = $this->matchTypeForNullableUnionType($phpStanType);
            if ($nullabledType === null) {
                // use first unioned type in case of unioned object types
                return $this->matchTypeForUnionedObjectTypes($phpStanType);
            }

            $nullabledTypeNode = $this->mapToPhpParserNode($nullabledType);
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

        if ($phpStanType instanceof NeverType ||
            $phpStanType instanceof VoidType ||
            $phpStanType instanceof MixedType ||
            $phpStanType instanceof ResourceType ||
            $phpStanType instanceof NullType
        ) {
            return null;
        }

        if ($phpStanType instanceof ObjectWithoutClassType) {
            if ($this->phpVersionProvider->isAtLeast(PhpVersionFeature::OBJECT_TYPE)) {
                return new Identifier('object');
            }

            return null;
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($phpStanType));
    }

    private function convertUnionArrayTypeNodesToArrayTypeOfUnionTypeNodes(
        UnionTypeNode $unionTypeNode
    ): AttributeAwareUnionTypeNode {
        $unionedArrayType = [];
        foreach ($unionTypeNode->types as $unionedType) {
            if ($unionedType instanceof UnionTypeNode) {
                foreach ($unionedType->types as $key => $subUnionedType) {
                    $unionedType->types[$key] = new ArrayTypeNode($subUnionedType);
                }

                $unionedArrayType[] = $unionedType;
                continue;
            }

            $unionedArrayType[] = new ArrayTypeNode($unionedType);
        }

        return new AttributeAwareUnionTypeNode($unionedArrayType);
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
     * @return Name|Name\FullyQualified|PhpParserUnionType|null
     */
    private function matchTypeForUnionedObjectTypes(UnionType $unionType): ?Node
    {
        $phpParserUnionType = $this->matchPhpParserUnionType($unionType);
        if ($phpParserUnionType !== null) {
            return $phpParserUnionType;
        }

        // do the type should be compatible with all other types, e.g. A extends B, B
        foreach ($unionType->getTypes() as $unionedType) {
            if (! $unionedType instanceof TypeWithClassName) {
                return null;
            }

            foreach ($unionType->getTypes() as $nestedUnionedType) {
                if (! $nestedUnionedType instanceof TypeWithClassName) {
                    return null;
                }

                if (! $this->areTypeWithClassNamesRelated($unionedType, $nestedUnionedType)) {
                    continue 2;
                }
            }

            return new Name\FullyQualified($unionedType->getClassName());
        }

        return null;
    }

    private function areTypeWithClassNamesRelated(TypeWithClassName $firstType, TypeWithClassName $secondType): bool
    {
        if (is_a($firstType->getClassName(), $secondType->getClassName(), true)) {
            return true;
        }

        return is_a($secondType->getClassName(), $firstType->getClassName(), true);
    }

    private function matchArrayTypes(UnionType $unionType): ?Identifier
    {
        $isNullableType = false;
        $hasIterable = false;

        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType instanceof IterableType) {
                $hasIterable = true;
                continue;
            }

            if ($unionedType instanceof ArrayType) {
                continue;
            }

            if ($unionedType instanceof NullType) {
                $isNullableType = true;
                continue;
            }

            if ($unionedType instanceof ObjectType) {
                if ($unionedType->getClassName() === Traversable::class) {
                    $hasIterable = true;
                    continue;
                }
            }

            return null;
        }

        $type = $hasIterable ? 'iterable' : 'array';
        if ($isNullableType) {
            return new Identifier('?' . $type);
        }

        return new Identifier($type);
    }

    private function matchPhpParserUnionType(UnionType $unionType): ?PhpParserUnionType
    {
        if (! $this->phpVersionProvider->isAtLeast(PhpVersionFeature::UNION_TYPES)) {
            return null;
        }

        $phpParserUnionedTypes = [];
        foreach ($unionType->getTypes() as $unionedType) {
            /** @var Identifier|Name|null $phpParserNode */
            $phpParserNode = $this->mapToPhpParserNode($unionedType);
            if ($phpParserNode === null) {
                return null;
            }

            $phpParserUnionedTypes[] = $phpParserNode;
        }

        return new PhpParserUnionType($phpParserUnionedTypes);
    }
}
