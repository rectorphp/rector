<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\NameTypeResolver\NameTypeResolverTest
 */
final class NameTypeResolver implements NodeTypeResolverInterface
{
    public function __construct(
        private ReflectionProvider $reflectionProvider
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
        if ($node->toString() === 'parent') {
            return $this->resolveParent($node);
        }

        $fullyQualifiedName = $this->resolveFullyQualifiedName($node);
        return new ObjectType($fullyQualifiedName);
    }

    private function resolveParent(Name $name): MixedType | ObjectType | UnionType
    {
        $className = $name->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
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
        if (in_array($nameValue, ['self', 'static', 'this'], true)) {
            /** @var string|null $class */
            $class = $name->getAttribute(AttributeKey::CLASS_NAME);
            if ($class === null) {
                // anonymous class probably
                return 'Anonymous';
            }

            return $class;
        }

        /** @var Name|null $resolvedNameNode */
        $resolvedNameNode = $name->getAttribute(AttributeKey::RESOLVED_NAME);
        if ($resolvedNameNode instanceof Name) {
            return $resolvedNameNode->toString();
        }

        return $nameValue;
    }
}
