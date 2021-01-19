<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\NodeTypeResolver\Reflection\ClassReflectionTypesResolver;

final class ClassAndInterfaceTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @var ClassReflectionTypesResolver
     */
    private $classReflectionTypesResolver;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    public function __construct(ClassReflectionTypesResolver $classReflectionTypesResolver, TypeFactory $typeFactory)
    {
        $this->classReflectionTypesResolver = $classReflectionTypesResolver;
        $this->typeFactory = $typeFactory;
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
     */
    public function resolve(Node $node): Type
    {
        $nodeScope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $nodeScope instanceof Scope) {
            // new node probably
            return new MixedType();
        }

        $classReflection = $nodeScope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return new MixedType();
        }

        $classTypes = $this->classReflectionTypesResolver->resolve($classReflection);

        return $this->typeFactory->createObjectTypeOrUnionType($classTypes);
    }
}
