<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeCorrector;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\NodeTypeResolver\Reflection\ClassReflectionTypesResolver;

final class ParentClassLikeTypeCorrector
{
    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    /**
     * @var ClassReflectionTypesResolver
     */
    private $classReflectionTypesResolver;

    public function __construct(
        ClassReflectionTypesResolver $classReflectionTypesResolver,
        ReflectionProvider $reflectionProvider,
        TypeFactory $typeFactory
    ) {
        $this->typeFactory = $typeFactory;
        $this->reflectionProvider = $reflectionProvider;
        $this->classReflectionTypesResolver = $classReflectionTypesResolver;
    }

    public function correct(Type $type): Type
    {
        if ($type instanceof TypeWithClassName) {
            if (! ClassExistenceStaticHelper::doesClassLikeExist($type->getClassName())) {
                return $type;
            }

            $allTypes = $this->getClassLikeTypesByClassName($type->getClassName());
            return $this->typeFactory->createObjectTypeOrUnionType($allTypes);
        }

        $allTypes = [];
        $classNames = TypeUtils::getDirectClassNames($type);
        foreach ($classNames as $className) {
            if (! ClassExistenceStaticHelper::doesClassLikeExist($className)) {
                continue;
            }

            $allTypes = array_merge($allTypes, $this->getClassLikeTypesByClassName($className));
        }

        return $this->typeFactory->createObjectTypeOrUnionType($allTypes);
    }

    /**
     * @return string[]
     */
    private function getClassLikeTypesByClassName(string $className): array
    {
        $classReflection = $this->reflectionProvider->getClass($className);
        $classLikeTypes = $this->classReflectionTypesResolver->resolve($classReflection);

        return array_unique($classLikeTypes);
    }
}
