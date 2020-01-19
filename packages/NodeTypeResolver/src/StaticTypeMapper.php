<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Analyser\NameScope;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\TypeNodeResolver;
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
use PHPStan\Type\ConstantType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\BetterPhpDocParser\Type\PreSlashStringType;
use Rector\Exception\NotImplementedException;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PHPStan\Type\ParentStaticType;
use Rector\PHPStan\Type\SelfObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier;

/**
 * Maps PhpParser <=> PHPStan <=> PHPStan doc <=> string type nodes between all possible formats
 */
final class StaticTypeMapper
{
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

    /**
     * @var TypeNodeResolver
     */
    private $typeNodeResolver;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(
        TypeFactory $typeFactory,
        ObjectTypeSpecifier $objectTypeSpecifier,
        PHPStanStaticTypeMapper $phpStanStaticTypeMapper,
        TypeNodeResolver $typeNodeResolver,
        NameResolver $nameResolver
    ) {
        $this->typeFactory = $typeFactory;
        $this->objectTypeSpecifier = $objectTypeSpecifier;
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
        $this->typeNodeResolver = $typeNodeResolver;
        $this->nameResolver = $nameResolver;
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
        return $this->phpStanStaticTypeMapper->mapToDocString($phpStanType, $parentType);
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

        if ($type instanceof UnionType) {
            $types = $type->getTypes();
            sort($types);
            $type = new UnionType($types);
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
        $nameScope = $this->createNameScopeFromNode($node);
        // @todo use in the future

        $type = $this->typeNodeResolver->resolve($typeNode, $nameScope);
        if ($typeNode instanceof GenericTypeNode) {
            return $type;
        }

        return $this->customMapPHPStanPhpDocTypeNodeToPHPStanType($typeNode, $node);
    }

    /**
     * @deprecated Move gradualy to @see \PHPStan\PhpDoc\TypeNodeResolver from PHPStan
     */
    private function customMapPHPStanPhpDocTypeNodeToPHPStanType(TypeNode $typeNode, Node $node): Type
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

    /**
     * @see https://github.com/phpstan/phpstan-src/blob/8376548f76e2c845ae047e3010e873015b796818/src/Analyser/NameScope.php#L32
     */
    private function createNameScopeFromNode(Node $node): NameScope
    {
        $namespace = $node->getAttribute(AttributeKey::NAMESPACE_NAME);
        $useNodes = $node->getAttribute(AttributeKey::USE_NODES);

        $uses = [];
        if ($useNodes) {
            foreach ($useNodes as $useNode) {
                foreach ($useNode->uses as $useUse) {
                    /** @var UseUse $useUse */
                    $aliasName = $useUse->getAlias()->name;
                    $useName = $this->nameResolver->getName($useUse->name);

                    $uses[$aliasName] = $useName;
                }
            }
        }

        $className = $node->getAttribute(AttributeKey::CLASS_NAME);

        return new NameScope($namespace, $uses, $className);
    }
}
