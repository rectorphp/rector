<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use Closure;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\FloatType;
use PHPStan\Type\Generic\GenericObjectType;
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
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\BetterPhpDocParser\Type\PreSlashStringType;
use Rector\Exception\NotImplementedException;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\Php\PhpVersionProvider;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PHPStan\Type\ParentStaticType;
use Rector\PHPStan\Type\SelfObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier;

/**
 * Maps PhpParser <=> PHPStan <=> PHPStan doc <=> string type nodes to between all possible formats
 */
final class StaticTypeMapper
{
    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var ObjectTypeSpecifier
     */
    private $objectTypeSpecifier;

    /**
     * @var PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;

    public function __construct(
        PhpVersionProvider $phpVersionProvider,
        TypeFactory $typeFactory,
        ObjectTypeSpecifier $objectTypeSpecifier,
        PHPStanStaticTypeMapper $phpStanStaticTypeMapper
    ) {
        $this->phpVersionProvider = $phpVersionProvider;
        $this->typeFactory = $typeFactory;
        $this->objectTypeSpecifier = $objectTypeSpecifier;
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }

    public function mapPHPStanTypeToPHPStanPhpDocTypeNode(Type $phpStanType): TypeNode
    {
        return $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($phpStanType);
    }

    /**
     * @return Identifier|Name|NullableType|PhpParserUnionType|null
     */
    public function mapPHPStanTypeToPhpParserNode(Type $phpStanType, ?string $kind = null): ?Node
    {
        return $this->phpStanStaticTypeMapper->mapToPhpParserNode($phpStanType, $kind);
    }

    public function mapPHPStanTypeToDocString(Type $phpStanType, ?Type $parentType = null): string
    {
        if ($phpStanType instanceof UnionType || $phpStanType instanceof IntersectionType) {
            $stringTypes = [];

            foreach ($phpStanType->getTypes() as $unionedType) {
                $stringTypes[] = $this->mapPHPStanTypeToDocString($unionedType);
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

        if ($phpStanType instanceof StringType) {
            return 'string';
        }

        if ($phpStanType instanceof IntegerType) {
            return 'int';
        }

        if ($phpStanType instanceof NullType) {
            return 'null';
        }

        if ($phpStanType instanceof ArrayType) {
            if ($phpStanType->getItemType() instanceof UnionType) {
                $unionedTypesAsString = [];
                foreach ($phpStanType->getItemType()->getTypes() as $unionedArrayItemType) {
                    $unionedTypesAsString[] = $this->mapPHPStanTypeToDocString(
                        $unionedArrayItemType,
                        $phpStanType
                    ) . '[]';
                }

                $unionedTypesAsString = array_values($unionedTypesAsString);
                $unionedTypesAsString = array_unique($unionedTypesAsString);

                return implode('|', $unionedTypesAsString);
            }

            $docString = $this->mapPHPStanTypeToDocString($phpStanType->getItemType(), $parentType);

            // @todo improve this
            $docStringTypes = explode('|', $docString);
            $docStringTypes = array_filter($docStringTypes);

            foreach ($docStringTypes as $key => $docStringType) {
                $docStringTypes[$key] = $docStringType . '[]';
            }

            return implode('|', $docStringTypes);
        }

        if ($phpStanType instanceof MixedType) {
            return 'mixed';
        }

        if ($phpStanType instanceof FloatType) {
            return 'float';
        }

        if ($phpStanType instanceof VoidType) {
            if ($this->phpVersionProvider->isAtLeast('7.1')) {
                // the void type is better done in PHP code
                return '';
            }

            // fallback for PHP 7.0 and older, where void type was only in docs
            return 'void';
        }

        if ($phpStanType instanceof BooleanType) {
            return 'bool';
        }

        if ($phpStanType instanceof IterableType) {
            if ($this->phpVersionProvider->isAtLeast('7.1')) {
                // the void type is better done in PHP code
                return '';
            }

            return 'iterable';
        }

        if ($phpStanType instanceof NeverType) {
            return 'mixed';
        }

        if ($phpStanType instanceof CallableType) {
            return 'callable';
        }

        if ($phpStanType instanceof ResourceType) {
            return 'resource';
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($phpStanType));
    }

    public function mapPhpParserNodePHPStanType(Node $node): Type
    {
        if ($node instanceof Expr) {
            /** @var Scope $scope */
            $scope = $node->getAttribute(AttributeKey::SCOPE);

            return $scope->getType($node);
        }

        if ($node instanceof NullableType) {
            $types = [];
            $types[] = $this->mapPhpParserNodePHPStanType($node->type);
            $types[] = new NullType();

            return $this->typeFactory->createMixedPassedOrUnionType($types);
        }

        if ($node instanceof Identifier) {
            if ($node->name === 'string') {
                return new StringType();
            }

            $type = $this->mapScalarStringToType($node->name);
            if ($type !== null) {
                return $type;
            }
        }

        if ($node instanceof FullyQualified) {
            return new FullyQualifiedObjectType($node->toString());
        }

        if ($node instanceof Name) {
            $name = $node->toString();

            if (ClassExistenceStaticHelper::doesClassLikeExist($name)) {
                return new FullyQualifiedObjectType($node->toString());
            }

            return new MixedType();
        }

        throw new NotImplementedException(__METHOD__ . 'for type ' . get_class($node));
    }

    public function mapPHPStanPhpDocTypeToPHPStanType(PhpDocTagValueNode $phpDocTagValueNode, Node $node): Type
    {
        if ($phpDocTagValueNode instanceof ReturnTagValueNode ||
            $phpDocTagValueNode instanceof ParamTagValueNode ||
            $phpDocTagValueNode instanceof VarTagValueNode
        ) {
            return $this->mapPHPStanPhpDocTypeNodeToPHPStanType($phpDocTagValueNode->type, $node);
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($phpDocTagValueNode));
    }

    public function createTypeHash(Type $type): string
    {
        if ($type instanceof MixedType) {
            return serialize($type);
        }

        if ($type instanceof ArrayType) {
            // @todo sort to make different order identical
            return $this->createTypeHash($type->getItemType()) . '[]';
        }

        if ($type instanceof ShortenedObjectType) {
            return $type->getFullyQualifiedName();
        }

        if ($type instanceof FullyQualifiedObjectType || $type instanceof ObjectType) {
            return $type->getClassName();
        }

        if ($type instanceof ConstantType) {
            if (method_exists($type, 'getValue')) {
                return get_class($type) . $type->getValue();
            }

            throw new ShouldNotHappenException();
        }

        return $this->mapPHPStanTypeToDocString($type);
    }

    /**
     * @return Identifier|Name|NullableType|null
     */
    public function mapStringToPhpParserNode(string $type): ?Node
    {
        if ($type === 'string') {
            return new Identifier('string');
        }

        if ($type === 'int') {
            return new Identifier('int');
        }

        if ($type === 'array') {
            return new Identifier('array');
        }

        if ($type === 'float') {
            return new Identifier('float');
        }

        if (Strings::contains($type, '\\') || ctype_upper($type[0])) {
            return new FullyQualified($type);
        }

        if (Strings::startsWith($type, '?')) {
            $nullableType = ltrim($type, '?');

            /** @var Identifier|Name $nameNode */
            $nameNode = $this->mapStringToPhpParserNode($nullableType);

            return new NullableType($nameNode);
        }

        if ($type === 'void') {
            return new Identifier('void');
        }

        throw new NotImplementedException(sprintf('%s for "%s"', __METHOD__, $type));
    }

    public function mapPHPStanPhpDocTypeNodeToPhpDocString(TypeNode $typeNode, Node $node): string
    {
        $phpStanType = $this->mapPHPStanPhpDocTypeNodeToPHPStanType($typeNode, $node);

        return $this->mapPHPStanTypeToDocString($phpStanType);
    }

    public function mapPHPStanPhpDocTypeNodeToPHPStanType(TypeNode $typeNode, Node $node): Type
    {
        if ($typeNode instanceof IdentifierTypeNode) {
            $type = $this->mapScalarStringToType($typeNode->name);
            if ($type !== null) {
                return $type;
            }

            $loweredName = strtolower($typeNode->name);
            if ($loweredName === '\string') {
                return new PreSlashStringType();
            }

            if ($loweredName === 'class-string') {
                return new ClassStringType();
            }

            if ($loweredName === 'self') {
                /** @var string|null $className */
                $className = $node->getAttribute(AttributeKey::CLASS_NAME);
                if ($className === null) {
                    // self outside the class, e.g. in a function
                    return new MixedType();
                }

                return new SelfObjectType($className);
            }

            if ($loweredName === 'parent') {
                /** @var string|null $parentClassName */
                $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
                if ($parentClassName === null) {
                    return new MixedType();
                }

                return new ParentStaticType($parentClassName);
            }

            if ($loweredName === 'static') {
                /** @var string|null $className */
                $className = $node->getAttribute(AttributeKey::CLASS_NAME);
                if ($className === null) {
                    return new MixedType();
                }

                return new StaticType($className);
            }

            if ($loweredName === 'iterable') {
                return new IterableType(new MixedType(), new MixedType());
            }

            // @todo improve - making many false positives now
            $objectType = new ObjectType($typeNode->name);

            return $this->objectTypeSpecifier->narrowToFullyQualifiedOrAlaisedObjectType($node, $objectType);
        }

        if ($typeNode instanceof ArrayTypeNode) {
            $nestedType = $this->mapPHPStanPhpDocTypeNodeToPHPStanType($typeNode->type, $node);

            return new ArrayType(new MixedType(), $nestedType);
        }

        if ($typeNode instanceof UnionTypeNode || $typeNode instanceof IntersectionTypeNode) {
            $unionedTypes = [];
            foreach ($typeNode->types as $unionedTypeNode) {
                $unionedTypes[] = $this->mapPHPStanPhpDocTypeNodeToPHPStanType($unionedTypeNode, $node);
            }

            // to prevent missing class error, e.g. in tests
            return $this->typeFactory->createMixedPassedOrUnionType($unionedTypes);
        }

        if ($typeNode instanceof ThisTypeNode) {
            if ($node === null) {
                throw new ShouldNotHappenException();
            }
            /** @var string $className */
            $className = $node->getAttribute(AttributeKey::CLASS_NAME);

            return new ThisType($className);
        }

        if ($typeNode instanceof GenericTypeNode) {
            $genericMainType = $this->mapPHPStanPhpDocTypeNodeToPHPStanType($typeNode->type, $node);

            if ($genericMainType instanceof TypeWithClassName) {
                $mainTypeAsString = $genericMainType->getClassName();
            } else {
                $mainTypeAsString = $typeNode->type->name;
            }

            $genericTypes = [];
            foreach ($typeNode->genericTypes as $genericTypeNode) {
                $genericTypes[] = $this->mapPHPStanPhpDocTypeNodeToPHPStanType($genericTypeNode, $node);
            }

            // special use case for array
            if (in_array($mainTypeAsString, ['array', 'iterable'], true)) {
                $genericType = $this->typeFactory->createMixedPassedOrUnionType($genericTypes);

                if ($mainTypeAsString === 'array') {
                    return new ArrayType(new MixedType(), $genericType);
                }

                if ($mainTypeAsString === 'iterable') {
                    return new IterableType(new MixedType(), $genericType);
                }
            }

            return new GenericObjectType($mainTypeAsString, $genericTypes);
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($typeNode));
    }

    private function mapScalarStringToType(string $scalarName): ?Type
    {
        $loweredScalarName = Strings::lower($scalarName);
        if ($loweredScalarName === 'string') {
            return new StringType();
        }

        if (in_array($loweredScalarName, ['float', 'real', 'double'], true)) {
            return new FloatType();
        }

        if (in_array($loweredScalarName, ['int', 'integer'], true)) {
            return new IntegerType();
        }

        if (in_array($loweredScalarName, ['false', 'true', 'bool', 'boolean'], true)) {
            return new BooleanType();
        }

        if ($loweredScalarName === 'array') {
            return new ArrayType(new MixedType(), new MixedType());
        }

        if ($loweredScalarName === 'null') {
            return new NullType();
        }

        if ($loweredScalarName === 'void') {
            return new VoidType();
        }

        if ($loweredScalarName === 'object') {
            return new ObjectWithoutClassType();
        }

        if ($loweredScalarName === 'resource') {
            return new ResourceType();
        }

        if (in_array($loweredScalarName, ['callback', 'callable'], true)) {
            return new CallableType();
        }

        if ($loweredScalarName === 'mixed') {
            return new MixedType(true);
        }

        return null;
    }
}
