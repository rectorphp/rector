<?php

declare (strict_types=1);
namespace Rector\Core\Util\Reflection;

use Rector\Core\Exception\Reflection\MissingPrivatePropertyException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
/**
 * @see \Rector\Core\Tests\Util\Reflection\PrivatesAccessorTest
 */
final class PrivatesAccessor
{
    /**
     * @param callable(mixed $value): mixed $closure
     */
    public function propertyClosure(object $object, string $propertyName, callable $closure) : void
    {
        $property = $this->getPrivateProperty($object, $propertyName);
        // modify value
        $property = $closure($property);
        $this->setPrivateProperty($object, $propertyName, $property);
    }
    /**
     * @param object|class-string $object
     * @param mixed[] $arguments
     * @api
     * @return mixed
     */
    public function callPrivateMethod($object, string $methodName, array $arguments)
    {
        if (\is_string($object)) {
            $reflectionClass = new ReflectionClass($object);
            $object = $reflectionClass->newInstanceWithoutConstructor();
        }
        $reflectionMethod = $this->createAccessibleMethodReflection($object, $methodName);
        return $reflectionMethod->invokeArgs($object, $arguments);
    }
    /**
     * @return mixed
     */
    public function getPrivateProperty(object $object, string $propertyName)
    {
        $reflectionProperty = $this->resolvePropertyReflection($object, $propertyName);
        $reflectionProperty->setAccessible(\true);
        return $reflectionProperty->getValue($object);
    }
    /**
     * @param mixed $value
     */
    public function setPrivateProperty(object $object, string $propertyName, $value) : void
    {
        $reflectionProperty = $this->resolvePropertyReflection($object, $propertyName);
        $reflectionProperty->setAccessible(\true);
        $reflectionProperty->setValue($object, $value);
    }
    private function createAccessibleMethodReflection(object $object, string $methodName) : ReflectionMethod
    {
        $reflectionClass = new ReflectionClass(\get_class($object));
        $reflectionMethod = $reflectionClass->getMethod($methodName);
        $reflectionMethod->setAccessible(\true);
        return $reflectionMethod;
    }
    private function resolvePropertyReflection(object $object, string $propertyName) : ReflectionProperty
    {
        if (\property_exists($object, $propertyName)) {
            return new ReflectionProperty($object, $propertyName);
        }
        $parentClass = \get_parent_class($object);
        if ($parentClass !== \false) {
            return new ReflectionProperty($parentClass, $propertyName);
        }
        $errorMessage = \sprintf('Property "$%s" was not found in "%s" class', $propertyName, \get_class($object));
        throw new MissingPrivatePropertyException($errorMessage);
    }
}
