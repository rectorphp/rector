<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\AnnotationReader;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\Annotation;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Resolver\NameResolver;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\Validator\Constraint;
use Throwable;

final class NodeAnnotationReader
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(Reader $reader, NameResolver $nameResolver)
    {
        $this->reader = $reader;
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
            return $this->reader->getMethodAnnotation($reflectionMethod, $annotationClassName);
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

        return $this->reader->getClassAnnotation($classReflection, $annotationClassName);
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
        return $this->reader->getPropertyAnnotation($propertyReflection, $annotationClassName);
    }

    private function createClassReflectionFromNode(Class_ $class): ReflectionClass
    {
        /** @var string $className */
        $className = $this->nameResolver->getName($class);

        return new ReflectionClass($className);
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
            // in case of PHPUnit property or just-added property
            return null;
        }
    }
}
