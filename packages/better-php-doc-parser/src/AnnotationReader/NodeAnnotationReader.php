<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\AnnotationReader;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Node\Resolver\NodeNameResolver;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Throwable;

final class NodeAnnotationReader
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(Reader $reader, NodeNameResolver $nodeNameResolver)
    {
        $this->reader = $reader;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @return object|null
     */
    public function readMethodAnnotation(ClassMethod $classMethod, string $annotationClassName)
    {
        /** @var string $className */
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);

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
     * @return object|null
     */
    public function readPropertyAnnotation(Property $property, string $annotationClassName)
    {
        $propertyReflection = $this->createPropertyReflectionFromPropertyNode($property);
        if ($propertyReflection === null) {
            return null;
        }

        return $this->reader->getPropertyAnnotation($propertyReflection, $annotationClassName);
    }

    private function createClassReflectionFromNode(Class_ $class): ReflectionClass
    {
        /** @var string $className */
        $className = $this->nodeNameResolver->getName($class);

        return new ReflectionClass($className);
    }

    private function createPropertyReflectionFromPropertyNode(Property $property): ?ReflectionProperty
    {
        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);

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
