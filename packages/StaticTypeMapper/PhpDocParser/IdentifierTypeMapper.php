<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpDocParser;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\NameScope;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
use Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
use Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
use Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier;
final class IdentifierTypeMapper implements \Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface
{
    /**
     * @var \Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier
     */
    private $objectTypeSpecifier;
    /**
     * @var \Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper
     */
    private $scalarStringToTypeMapper;
    /**
     * @var \Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver
     */
    private $parentClassScopeResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier $objectTypeSpecifier, \Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper $scalarStringToTypeMapper, \Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver $parentClassScopeResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->objectTypeSpecifier = $objectTypeSpecifier;
        $this->scalarStringToTypeMapper = $scalarStringToTypeMapper;
        $this->parentClassScopeResolver = $parentClassScopeResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return class-string<TypeNode>
     */
    public function getNodeType() : string
    {
        return \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode::class;
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\Type\TypeNode $typeNode
     * @param \PhpParser\Node $node
     * @param \PHPStan\Analyser\NameScope $nameScope
     */
    public function mapToPHPStanType($typeNode, $node, $nameScope) : \PHPStan\Type\Type
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
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if ($loweredName === \Rector\Core\Enum\ObjectReference::SELF()->getValue()) {
            return $this->mapSelf($node);
        }
        if ($loweredName === \Rector\Core\Enum\ObjectReference::PARENT()->getValue()) {
            return $this->mapParent($scope);
        }
        if ($loweredName === \Rector\Core\Enum\ObjectReference::STATIC()->getValue()) {
            return $this->mapStatic($scope);
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
        $classLike = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return new \PHPStan\Type\MixedType();
        }
        // @todo check FQN
        $className = $this->nodeNameResolver->getName($classLike);
        if (!\is_string($className)) {
            // self outside the class, e.g. in a function
            return new \PHPStan\Type\MixedType();
        }
        return new \Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType($className);
    }
    /**
     * @return \PHPStan\Type\MixedType|\Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType
     */
    private function mapParent(\PHPStan\Analyser\Scope $scope)
    {
        $parentClassReflection = $this->parentClassScopeResolver->resolveParentClassReflection($scope);
        if (!$parentClassReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return new \PHPStan\Type\MixedType();
        }
        return new \Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType($parentClassReflection);
    }
    /**
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\StaticType
     */
    private function mapStatic(\PHPStan\Analyser\Scope $scope)
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return new \PHPStan\Type\MixedType();
        }
        return new \PHPStan\Type\StaticType($classReflection);
    }
}
