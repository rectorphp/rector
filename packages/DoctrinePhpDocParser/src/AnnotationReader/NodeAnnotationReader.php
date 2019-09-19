<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\AnnotationReader;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Annotation;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\Testing\PHPUnit\PHPUnitEnvironment;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
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
     * @return Template|Route|null
     */
    public function readMethodAnnotation(ClassMethod $classMethod, string $annotationClassName)
    {
        /** @var string $className */
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);

        /** @var string $methodName */
        $methodName = $this->nameResolver->getName($classMethod);

        $reflectionMethod = new ReflectionMethod($className, $methodName);

        return $this->annotationReader->getMethodAnnotation($reflectionMethod, $annotationClassName);
    }

    public function readClassAnnotation(Class_ $class, string $annotationClassName): Annotation
    {
        $classReflection = $this->createClassReflectionFromNode($class);

        /** @var Annotation|null $classAnnotation */
        $classAnnotation = $this->annotationReader->getClassAnnotation($classReflection, $annotationClassName);
        if ($classAnnotation === null) {
            throw new ShouldNotHappenException(__METHOD__ . '() on line ' . __LINE__);
        }

        return $classAnnotation;
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
            throw new ShouldNotHappenException(__METHOD__ . '() on line ' . __LINE__);
        }

        return $propertyAnnotation;
    }

    private function createPropertyReflectionFromPropertyNode(Property $property): ?ReflectionProperty
    {
        /** @var string $propertyName */
        $propertyName = $this->nameResolver->getName($property);

        /** @var string $className */
        $className = $property->getAttribute(AttributeKey::CLASS_NAME);

        if ($className === null || ! ClassExistenceStaticHelper::doesClassLikeExist($className)) {
            throw new ShouldNotHappenException(sprintf(
                'Class "%s" for property "%s" was not found.',
                (string) $className,
                $propertyName
            ));
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
