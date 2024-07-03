<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpDocParser;

use RectorPrefix202407\Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Enum\ObjectReference;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
use Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
use Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
use Rector\TypeDeclaration\PHPStan\ObjectTypeSpecifier;
/**
 * @implements PhpDocTypeMapperInterface<IdentifierTypeNode>
 */
final class IdentifierPhpDocTypeMapper implements PhpDocTypeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\PHPStan\ObjectTypeSpecifier
     */
    private $objectTypeSpecifier;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper
     */
    private $scalarStringToTypeMapper;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ObjectTypeSpecifier $objectTypeSpecifier, ScalarStringToTypeMapper $scalarStringToTypeMapper, ReflectionProvider $reflectionProvider, ReflectionResolver $reflectionResolver)
    {
        $this->objectTypeSpecifier = $objectTypeSpecifier;
        $this->scalarStringToTypeMapper = $scalarStringToTypeMapper;
        $this->reflectionProvider = $reflectionProvider;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getNodeType() : string
    {
        return IdentifierTypeNode::class;
    }
    /**
     * @param IdentifierTypeNode $typeNode
     */
    public function mapToPHPStanType(TypeNode $typeNode, Node $node, NameScope $nameScope) : Type
    {
        return $this->mapIdentifierTypeNode($typeNode, $node);
    }
    public function mapIdentifierTypeNode(IdentifierTypeNode $identifierTypeNode, Node $node) : Type
    {
        $type = $this->scalarStringToTypeMapper->mapScalarStringToType($identifierTypeNode->name);
        if (!$type instanceof MixedType) {
            return $type;
        }
        if ($type->isExplicitMixed()) {
            return $type;
        }
        $loweredName = \strtolower($identifierTypeNode->name);
        if ($loweredName === ObjectReference::SELF) {
            return $this->mapSelf($node);
        }
        if ($loweredName === ObjectReference::PARENT) {
            return $this->mapParent($node);
        }
        if ($loweredName === ObjectReference::STATIC) {
            return $this->mapStatic($node);
        }
        if ($loweredName === 'iterable') {
            return new IterableType(new MixedType(), new MixedType());
        }
        if (\strncmp($identifierTypeNode->name, '\\', \strlen('\\')) === 0) {
            $typeWithoutPreslash = Strings::substring($identifierTypeNode->name, 1);
            $objectType = new FullyQualifiedObjectType($typeWithoutPreslash);
        } else {
            if ($identifierTypeNode->name === 'scalar') {
                // pseudo type, see https://www.php.net/manual/en/language.types.intro.php
                $scalarTypes = [new BooleanType(), new StringType(), new IntegerType(), new FloatType()];
                return new UnionType($scalarTypes);
            }
            $objectType = new ObjectType($identifierTypeNode->name);
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        return $this->objectTypeSpecifier->narrowToFullyQualifiedOrAliasedObjectType($node, $objectType, $scope);
    }
    /**
     * @return \PHPStan\Type\MixedType|\Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType
     */
    private function mapSelf(Node $node)
    {
        // @todo check FQN
        $className = $this->resolveClassName($node);
        if (!\is_string($className)) {
            // self outside the class, e.g. in a function
            return new MixedType();
        }
        return new SelfObjectType($className);
    }
    /**
     * @return \Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType|\PHPStan\Type\MixedType
     */
    private function mapParent(Node $node)
    {
        $className = $this->resolveClassName($node);
        if (!\is_string($className)) {
            // parent outside the class, e.g. in a function
            return new MixedType();
        }
        /** @var ClassReflection $classReflection */
        $classReflection = $this->reflectionProvider->getClass($className);
        $parentClassReflection = $classReflection->getParentClass();
        if (!$parentClassReflection instanceof ClassReflection) {
            return new MixedType();
        }
        return new ParentStaticType($parentClassReflection);
    }
    /**
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\StaticType
     */
    private function mapStatic(Node $node)
    {
        $className = $this->resolveClassName($node);
        if (!\is_string($className)) {
            // static outside the class, e.g. in a function
            return new MixedType();
        }
        /** @var ClassReflection $classReflection */
        $classReflection = $this->reflectionProvider->getClass($className);
        return new StaticType($classReflection);
    }
    private function resolveClassName(Node $node) : ?string
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        return $classReflection->getName();
    }
}
