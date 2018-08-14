<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\Reflection\ClassReflectionTypesResolver;

final class ClassAndInterfaceTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var ClassReflectionTypesResolver
     */
    private $classReflectionTypesResolver;

    public function __construct(ClassReflectionTypesResolver $classReflectionTypesResolver)
    {
        $this->classReflectionTypesResolver = $classReflectionTypesResolver;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Class_::class, Interface_::class];
    }

    /**
     * @param Class_|Interface_ $node
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        /** @var Scope $nodeScope */
        $nodeScope = $node->getAttribute(Attribute::SCOPE);

        /** @var ClassReflection $classReflection */
        $classReflection = $nodeScope->getClassReflection();

        return $this->classReflectionTypesResolver->resolve($classReflection);
    }
}
