<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;
use PHPStan\Type\VoidType;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareUnionTypeNode;
use Rector\Exception\NotImplementedException;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\Php\PhpVersionProvider;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PHPStan\Type\SelfObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;
use Rector\PHPStanStaticTypeMapper\Contract\PHPStanStaticTypeMapperAwareInterface;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\ValueObject\PhpVersionFeature;

final class PHPStanStaticTypeMapper
{
    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    /**
     * @var TypeMapperInterface[]
     */
    private $typeMappers = [];

    /**
     * @param TypeMapperInterface[] $typeMappers
     */
    public function __construct(PhpVersionProvider $phpVersionProvider, array $typeMappers)
    {
        $this->phpVersionProvider = $phpVersionProvider;
        $this->typeMappers = $typeMappers;
    }

    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        if ($type instanceof ArrayType || $type instanceof IterableType) {
            $itemTypeNode = $this->mapToPHPStanPhpDocTypeNode($type->getItemType());

            if ($itemTypeNode instanceof UnionTypeNode) {
                return $this->convertUnionArrayTypeNodesToArrayTypeOfUnionTypeNodes($itemTypeNode);
            }

            return new ArrayTypeNode($itemTypeNode);
        }

        foreach ($this->typeMappers as $typeMapper) {
            if (! is_a($type, $typeMapper->getNodeClass(), true)) {
                continue;
            }

            // prevents circular dependency
            if ($typeMapper instanceof PHPStanStaticTypeMapperAwareInterface) {
                $typeMapper->setPHPStanStaticTypeMapper($this);
            }

            return $typeMapper->mapToPHPStanPhpDocTypeNode($type);
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($type));
    }

    /**
     * @return Identifier|Name|NullableType|PhpParserUnionType|null
     */
    public function mapToPhpParserNode(Type $phpStanType, ?string $kind = null): ?Node
    {
        if ($phpStanType instanceof SelfObjectType) {
            return new Identifier('self');
        }

        foreach ($this->typeMappers as $typeMapper) {
            // it cannot be is_a for SelfObjectType, because type classes inherit from each other
            if (! is_a($phpStanType, $typeMapper->getNodeClass(), true)) {
                continue;
            }

            return $typeMapper->mapToPhpParserNode($phpStanType, $kind);
        }

        if ($phpStanType instanceof ArrayType) {
            return new Identifier('array');
        }

        if ($phpStanType instanceof StaticType) {
            return null;
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

            return new FullyQualified($phpStanType->getClassName());
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($phpStanType));
    }

    public function mapToDocString(Type $phpStanType, ?Type $parentType = null): string
    {
        if ($phpStanType instanceof UnionType || $phpStanType instanceof IntersectionType) {
            $stringTypes = [];

            foreach ($phpStanType->getTypes() as $unionedType) {
                $stringTypes[] = $this->mapToDocString($unionedType);
            }

            // remove empty values, e.g. void/iterable
            $stringTypes = array_unique($stringTypes);
            $stringTypes = array_filter($stringTypes);

            $joinCharacter = $phpStanType instanceof IntersectionType ? '&' : '|';

            return implode($joinCharacter, $stringTypes);
        }

        if ($phpStanType instanceof AliasedObjectType) {
            // no preslash for alias
            return $phpStanType->getClassName();
        }

        if ($phpStanType instanceof ShortenedObjectType) {
            return '\\' . $phpStanType->getFullyQualifiedName();
        }

        if ($phpStanType instanceof FullyQualifiedObjectType) {
            // always prefixed with \\
            return '\\' . $phpStanType->getClassName();
        }

        if ($phpStanType instanceof ObjectType) {
            if (ClassExistenceStaticHelper::doesClassLikeExist($phpStanType->getClassName())) {
                return '\\' . $phpStanType->getClassName();
            }

            return $phpStanType->getClassName();
        }

        if ($phpStanType instanceof ObjectWithoutClassType) {
            return 'object';
        }

        if ($phpStanType instanceof ClosureType) {
            return '\\' . Closure::class;
        }

        if ($phpStanType instanceof StringType || $phpStanType instanceof NullType || $phpStanType instanceof IntegerType || $phpStanType instanceof MixedType || $phpStanType instanceof FloatType || $phpStanType instanceof CallableType || $phpStanType instanceof ResourceType) {
            return $phpStanType->describe(VerbosityLevel::typeOnly());
        }

        if ($phpStanType instanceof ArrayType) {
            if ($phpStanType->getItemType() instanceof UnionType) {
                $unionedTypesAsString = [];
                foreach ($phpStanType->getItemType()->getTypes() as $unionedArrayItemType) {
                    $unionedTypesAsString[] = $this->mapToDocString($unionedArrayItemType, $phpStanType) . '[]';
                }

                $unionedTypesAsString = array_values($unionedTypesAsString);
                $unionedTypesAsString = array_unique($unionedTypesAsString);

                return implode('|', $unionedTypesAsString);
            }

            $docString = $this->mapToDocString($phpStanType->getItemType(), $parentType);

            // @todo improve this
            $docStringTypes = explode('|', $docString);
            $docStringTypes = array_filter($docStringTypes);

            foreach ($docStringTypes as $key => $docStringType) {
                $docStringTypes[$key] = $docStringType . '[]';
            }

            return implode('|', $docStringTypes);
        }

        if ($phpStanType instanceof VoidType) {
            if ($this->phpVersionProvider->isAtLeast(PhpVersionFeature::SCALAR_TYPES)) {
                // the void type is better done in PHP code
                return '';
            }

            // fallback for PHP 7.0 and older, where void type was only in docs
            return 'void';
        }

        if ($phpStanType instanceof IterableType) {
            if ($this->phpVersionProvider->isAtLeast(PhpVersionFeature::SCALAR_TYPES)) {
                // the void type is better done in PHP code
                return '';
            }

            return 'iterable';
        }

        if ($phpStanType instanceof BooleanType) {
            return 'bool';
        }

        if ($phpStanType instanceof NeverType) {
            return 'mixed';
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
}
