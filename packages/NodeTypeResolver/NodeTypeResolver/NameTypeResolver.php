<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\NameTypeResolver\NameTypeResolverTest
 */
final class NameTypeResolver implements NodeTypeResolverInterface
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
        private BetterNodeFinder $betterNodeFinder,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses(): array
    {
        return [Name::class, FullyQualified::class];
    }

    /**
     * @param Name $node
     */
    public function resolve(Node $node): Type
    {
        if ($node->toString() === ObjectReference::PARENT()->getValue()) {
            return $this->resolveParent($node);
        }

        $fullyQualifiedName = $this->resolveFullyQualifiedName($node);

        if ($node->toString() === 'array') {
            return new ArrayType(new MixedType(), new MixedType());
        }

        return new ObjectType($fullyQualifiedName);
    }

    private function resolveParent(Name $name): MixedType | ObjectType | UnionType
    {
        $class = $this->betterNodeFinder->findParentType($name, Class_::class);
        if (! $class instanceof Class_) {
            return new MixedType();
        }

        $className = $this->nodeNameResolver->getName($class);
        if (! is_string($className)) {
            return new MixedType();
        }

        if (! $this->reflectionProvider->hasClass($className)) {
            return new MixedType();
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        $parentClassObjectTypes = [];
        foreach ($classReflection->getParents() as $parentClassReflection) {
            $parentClassObjectTypes[] = new ObjectType($parentClassReflection->getName());
        }

        if ($parentClassObjectTypes === []) {
            return new MixedType();
        }

        if (count($parentClassObjectTypes) === 1) {
            return $parentClassObjectTypes[0];
        }

        return new UnionType($parentClassObjectTypes);
    }

    private function resolveFullyQualifiedName(Name $name): string
    {
        $nameValue = $name->toString();

        if (in_array(
            $nameValue,
            [ObjectReference::SELF()->getValue(), ObjectReference::STATIC()->getValue(), 'this'],
            true
        )) {
            $classLike = $this->betterNodeFinder->findParentType($name, ClassLike::class);
            if (! $classLike instanceof ClassLike) {
                return $name->toString();
            }

            return (string) $this->nodeNameResolver->getName($classLike);
        }

        /** @var Name|null $resolvedNameNode */
        $resolvedNameNode = $name->getAttribute(AttributeKey::RESOLVED_NAME);
        if ($resolvedNameNode instanceof Name) {
            return $resolvedNameNode->toString();
        }

        return $nameValue;
    }
}
