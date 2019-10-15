<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\AnnotationReader;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Annotation;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\Testing\PHPUnit\PHPUnitEnvironment;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\Validator\Constraint;
use Throwable;

final class NodeAnnotationReader
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(AnnotationReader $annotationReader, NameResolver $nameResolver)
    {
        $this->annotationReader = $annotationReader;
        $this->nameResolver = $nameResolver;
    }

    /**
     * @return object|null
     */
    public function readMethodAnnotation(ClassMethod $classMethod, string $annotationClassName)
    {
        /** @var string $className */
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);

        /** @var string $methodName */
        $methodName = $this->nameResolver->getName($classMethod);

        $reflectionMethod = new ReflectionMethod($className, $methodName);

        try {
            return $this->annotationReader->getMethodAnnotation($reflectionMethod, $annotationClassName);
        } catch (AnnotationException $annotationException) {
            // unable to laod
            return null;
        }
    }

    /**
     * @return object|null
     */
    public function readClassAnnotation(Class_ $class, string $annotationClassName)
    {
        $classReflection = $this->createClassReflectionFromNode($class);

        return $this->annotationReader->getClassAnnotation($classReflection, $annotationClassName);
    }

    /**
     * @return Annotation|Constraint|null
     */
    public function readPropertyAnnotation(Property $property, string $annotationClassName)
    {
        $propertyReflection = $this->createPropertyReflectionFromPropertyNode($property);
        if ($propertyReflection === null) {
            return null;
        }

        /** @var Annotation|null $propertyAnnotation */
        $propertyAnnotation = $this->annotationReader->getPropertyAnnotation($propertyReflection, $annotationClassName);
        if ($propertyAnnotation === null) {
            return null;
        }

        return $propertyAnnotation;
    }

    private function createPropertyReflectionFromPropertyNode(Property $property): ?ReflectionProperty
    {
        /** @var string $propertyName */
        $propertyName = $this->nameResolver->getName($property);

        /** @var string|null $className */
        $className = $property->getAttribute(AttributeKey::CLASS_NAME);

        if ($className === null || ! ClassExistenceStaticHelper::doesClassLikeExist($className)) {
            // probably fresh node
            return null;
        }

        try {
            return new ReflectionProperty($className, $propertyName);
        } catch (Throwable $throwable) {
            if (PHPUnitEnvironment::isPHPUnitRun()) {
                return null;
            }

            throw new $throwable();
        }
    }

    private function createClassReflectionFromNode(Class_ $class): ReflectionClass
    {
        /** @var string $className */
        $className = $this->nameResolver->getName($class);

        return new ReflectionClass($className);
    }
}
