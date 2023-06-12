<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix202306\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements NodeTypeResolverInterface<ClassMethod|ClassConst>
 */
final class ClassMethodOrClassConstTypeResolver implements NodeTypeResolverInterface
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
        return [ClassMethod::class, ClassConst::class];
    }
    /**
     * @param ClassMethod|ClassConst $node
     */
    public function resolve(Node $node) : Type
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection || $classReflection->isAnonymous()) {
            // anonymous class
            return new ObjectWithoutClassType();
        }
        return new ObjectType($classReflection->getName());
    }
}
