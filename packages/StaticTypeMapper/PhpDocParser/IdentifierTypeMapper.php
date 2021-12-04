<?php

declare(strict_types=1);

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
final class IdentifierTypeMapper implements PhpDocTypeMapperInterface
{
    public function __construct(
        private readonly ObjectTypeSpecifier $objectTypeSpecifier,
        private readonly ScalarStringToTypeMapper $scalarStringToTypeMapper,
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly ReflectionProvider $reflectionProvider
    ) {
    }

    public function getNodeType(): string
    {
        return IdentifierTypeNode::class;
    }

    /**
     * @param IdentifierTypeNode $typeNode
     */
    public function mapToPHPStanType(TypeNode $typeNode, Node $node, NameScope $nameScope): Type
    {
        $type = $this->scalarStringToTypeMapper->mapScalarStringToType($typeNode->name);
        if (! $type instanceof MixedType) {
            return $type;
        }

        if ($type->isExplicitMixed()) {
            return $type;
        }

        $loweredName = strtolower($typeNode->name);

        if ($loweredName === 'class-string') {
            return new ClassStringType();
        }

        if ($loweredName === ObjectReference::SELF()->getValue()) {
            return $this->mapSelf($node);
        }

        if ($loweredName === ObjectReference::PARENT()->getValue()) {
            return $this->mapParent($node);
        }

        if ($loweredName === ObjectReference::STATIC()->getValue()) {
            return $this->mapStatic($node);
        }

        if ($loweredName === 'iterable') {
            return new IterableType(new MixedType(), new MixedType());
        }

        $objectType = new ObjectType($typeNode->name);

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        return $this->objectTypeSpecifier->narrowToFullyQualifiedOrAliasedObjectType($node, $objectType, $scope);
    }

    private function mapSelf(Node $node): MixedType | SelfObjectType
    {
        // @todo check FQN
        $className = $this->resolveClassName($node);
        if (! is_string($className)) {
            // self outside the class, e.g. in a function
            return new MixedType();
        }

        return new SelfObjectType($className);
    }

    private function mapParent(Node $node): ParentStaticType | MixedType
    {
        $className = $this->resolveClassName($node);
        if (! is_string($className)) {
            // parent outside the class, e.g. in a function
            return new MixedType();
        }

        /** @var ClassReflection $classReflection */
        $classReflection = $this->reflectionProvider->getClass($className);
        $parentClassReflection = $classReflection->getParentClass();

        if (! $parentClassReflection instanceof ClassReflection) {
            return new MixedType();
        }

        return new ParentStaticType($parentClassReflection);
    }

    private function mapStatic(Node $node): MixedType | StaticType
    {
        $className = $this->resolveClassName($node);
        if (! is_string($className)) {
            // static outside the class, e.g. in a function
            return new MixedType();
        }

        /** @var ClassReflection $classReflection */
        $classReflection = $this->reflectionProvider->getClass($className);
        return new StaticType($classReflection);
    }

    private function resolveClassName(Node $node): ?string
    {
        $classLike = $node instanceof ClassLike
            ? $node
            : $this->betterNodeFinder->findParentType($node, ClassLike::class);

        if (! $classLike instanceof ClassLike) {
            return null;
        }

        $className = $this->nodeNameResolver->getName($classLike);
        if (! is_string($className)) {
            return null;
        }

        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        return $className;
    }
}
