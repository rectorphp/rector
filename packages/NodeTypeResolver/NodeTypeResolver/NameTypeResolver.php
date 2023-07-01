<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use RectorPrefix202307\Symfony\Contracts\Service\Attribute\Required;
/**
 * @see \Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\NameTypeResolver\NameTypeResolverTest
 *
 * @implements NodeTypeResolverInterface<Name|FullyQualified>
 */
final class NameTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @required
     */
    public function autowire(ReflectionResolver $reflectionResolver) : void
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [Name::class, FullyQualified::class];
    }
    /**
     * @param Name $node
     */
    public function resolve(Node $node) : Type
    {
        if ($node->toString() === ObjectReference::PARENT) {
            return $this->resolveParent($node);
        }
        $fullyQualifiedName = $this->resolveFullyQualifiedName($node);
        if ($node->toString() === 'array') {
            return new ArrayType(new MixedType(), new MixedType());
        }
        return new ObjectType($fullyQualifiedName);
    }
    /**
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\ObjectType|\PHPStan\Type\UnionType
     */
    private function resolveParent(Name $name)
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($name);
        if (!$classReflection instanceof ClassReflection || !$classReflection->isClass()) {
            return new MixedType();
        }
        if ($classReflection->isAnonymous()) {
            return new MixedType();
        }
        $parentClassObjectTypes = [];
        foreach ($classReflection->getParents() as $parentClassReflection) {
            $parentClassObjectTypes[] = new ObjectType($parentClassReflection->getName());
        }
        if ($parentClassObjectTypes === []) {
            return new MixedType();
        }
        if (\count($parentClassObjectTypes) === 1) {
            return $parentClassObjectTypes[0];
        }
        return new UnionType($parentClassObjectTypes);
    }
    private function resolveFullyQualifiedName(Name $name) : string
    {
        $nameValue = $name->toString();
        if (\in_array($nameValue, [ObjectReference::SELF, ObjectReference::STATIC, 'this'], \true)) {
            $classReflection = $this->reflectionResolver->resolveClassReflection($name);
            if (!$classReflection instanceof ClassReflection || $classReflection->isAnonymous()) {
                return $name->toString();
            }
            return $classReflection->getName();
        }
        return $nameValue;
    }
}
