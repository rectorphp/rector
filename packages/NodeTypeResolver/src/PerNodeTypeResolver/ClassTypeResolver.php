<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Reflection\ClassReflectionTypesResolver;

final class ClassTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var ClassReflectionTypesResolver
     */
    private $classReflectionTypesResolver;

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Class_::class];
    }

    public function __construct(ClassReflectionTypesResolver $classReflectionTypesResolver)
    {
        $this->classReflectionTypesResolver = $classReflectionTypesResolver;
    }

    /**
     * @param Class_ $classNode
     * @return string[]
     */
    public function resolve(Node $classNode): array
    {
        /** @var Scope $classNodeScope */
        $classNodeScope = $classNode->getAttribute(Attribute::SCOPE);

        /** @var ClassReflection $classReflection */
        $classReflection = $classNodeScope->getClassReflection();

        return $this->classReflectionTypesResolver->resolve($classReflection);
    }
}
