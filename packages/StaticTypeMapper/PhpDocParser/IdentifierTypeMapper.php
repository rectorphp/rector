<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpDocParser;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
use Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
use Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
use Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier;
/**
 * @implements PhpDocTypeMapperInterface<IdentifierTypeNode>
 */
final class IdentifierTypeMapper implements \Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier
     */
    private $objectTypeSpecifier;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper
     */
    private $scalarStringToTypeMapper;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier $objectTypeSpecifier, \Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper $scalarStringToTypeMapper, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->objectTypeSpecifier = $objectTypeSpecifier;
        $this->scalarStringToTypeMapper = $scalarStringToTypeMapper;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getNodeType() : string
    {
        return \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode::class;
    }
    /**
     * @param IdentifierTypeNode $typeNode
     */
    public function mapToPHPStanType(\PHPStan\PhpDocParser\Ast\Type\TypeNode $typeNode, \PhpParser\Node $node, \PHPStan\Analyser\NameScope $nameScope) : \PHPStan\Type\Type
    {
        $type = $this->scalarStringToTypeMapper->mapScalarStringToType($typeNode->name);
        if (!$type instanceof \PHPStan\Type\MixedType) {
            return $type;
        }
        if ($type->isExplicitMixed()) {
            return $type;
        }
        $loweredName = \strtolower($typeNode->name);
        if ($loweredName === 'class-string') {
            return new \PHPStan\Type\ClassStringType();
        }
        if ($loweredName === \Rector\Core\Enum\ObjectReference::SELF()->getValue()) {
            return $this->mapSelf($node);
        }
        if ($loweredName === \Rector\Core\Enum\ObjectReference::PARENT()->getValue()) {
            return $this->mapParent($node);
        }
        if ($loweredName === \Rector\Core\Enum\ObjectReference::STATIC()->getValue()) {
            return $this->mapStatic($node);
        }
        if ($loweredName === 'iterable') {
            return new \PHPStan\Type\IterableType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType());
        }
        $objectType = new \PHPStan\Type\ObjectType($typeNode->name);
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        return $this->objectTypeSpecifier->narrowToFullyQualifiedOrAliasedObjectType($node, $objectType, $scope);
    }
    /**
     * @return \PHPStan\Type\MixedType|\Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType
     */
    private function mapSelf(\PhpParser\Node $node)
    {
        // @todo check FQN
        $className = $this->resolveClassName($node);
        if (!\is_string($className)) {
            // self outside the class, e.g. in a function
            return new \PHPStan\Type\MixedType();
        }
        return new \Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType($className);
    }
    /**
     * @return \PHPStan\Type\MixedType|\Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType
     */
    private function mapParent(\PhpParser\Node $node)
    {
        $className = $this->resolveClassName($node);
        if (!\is_string($className)) {
            // parent outside the class, e.g. in a function
            return new \PHPStan\Type\MixedType();
        }
        /** @var ClassReflection $classReflection */
        $classReflection = $this->reflectionProvider->getClass($className);
        $parentClassReflection = $classReflection->getParentClass();
        if (!$parentClassReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return new \PHPStan\Type\MixedType();
        }
        return new \Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType($parentClassReflection);
    }
    /**
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\StaticType
     */
    private function mapStatic(\PhpParser\Node $node)
    {
        $className = $this->resolveClassName($node);
        if (!\is_string($className)) {
            // static outside the class, e.g. in a function
            return new \PHPStan\Type\MixedType();
        }
        /** @var ClassReflection $classReflection */
        $classReflection = $this->reflectionProvider->getClass($className);
        return new \PHPStan\Type\StaticType($classReflection);
    }
    private function resolveClassName(\PhpParser\Node $node) : ?string
    {
        $classLike = $node instanceof \PhpParser\Node\Stmt\ClassLike ? $node : $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        $className = $this->nodeNameResolver->getName($classLike);
        if (!\is_string($className)) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        return $className;
    }
}
