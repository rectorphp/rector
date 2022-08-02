<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpDocParser;

use RectorPrefix202208\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
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
use Rector\Core\Enum\ObjectReference;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
use Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
use Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
use Rector\TypeDeclaration\PHPStan\ObjectTypeSpecifier;
/**
 * @implements PhpDocTypeMapperInterface<IdentifierTypeNode>
 */
final class IdentifierTypeMapper implements PhpDocTypeMapperInterface
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
    public function __construct(ObjectTypeSpecifier $objectTypeSpecifier, ScalarStringToTypeMapper $scalarStringToTypeMapper, BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider)
    {
        $this->objectTypeSpecifier = $objectTypeSpecifier;
        $this->scalarStringToTypeMapper = $scalarStringToTypeMapper;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
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
        $type = $this->scalarStringToTypeMapper->mapScalarStringToType($typeNode->name);
        if (!$type instanceof MixedType) {
            return $type;
        }
        if ($type->isExplicitMixed()) {
            return $type;
        }
        $loweredName = \strtolower($typeNode->name);
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
        if (\strncmp($typeNode->name, '\\', \strlen('\\')) === 0) {
            $typeWithoutPreslash = Strings::substring($typeNode->name, 1);
            $objectType = new FullyQualifiedObjectType($typeWithoutPreslash);
        } else {
            if ($typeNode->name === 'scalar') {
                // pseudo type, see https://www.php.net/manual/en/language.types.intro.php
                $scalarTypes = [new BooleanType(), new StringType(), new IntegerType(), new FloatType()];
                return new UnionType($scalarTypes);
            }
            $objectType = new ObjectType($typeNode->name);
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
        $classLike = $node instanceof ClassLike ? $node : $this->betterNodeFinder->findParentType($node, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
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
