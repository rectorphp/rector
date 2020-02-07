<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\NodeTypeResolver\Reflection\ClassReflectionTypesResolver;

final class ClassAndInterfaceTypeResolver implements PerNodeTypeResolverInterface
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
        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(AttributeKey::SCOPE);
        if ($nodeScope === null) {
            throw new ShouldNotHappenException();
        }

        /** @var ClassReflection $classReflection */
        $classReflection = $nodeScope->getClassReflection();

        $classTypes = $this->classReflectionTypesResolver->resolve($classReflection);

        return $this->typeFactory->createObjectTypeOrUnionType($classTypes);
    }
}
