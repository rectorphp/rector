<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\PackageBuilder\Reflection;

use ReflectionProperty;
use RectorPrefix202208\Symplify\PackageBuilder\Exception\InvalidPrivatePropertyTypeException;
use RectorPrefix202208\Symplify\PackageBuilder\Exception\MissingPrivatePropertyException;
/**
 * @api
 * @see \Symplify\PackageBuilder\Tests\Reflection\PrivatesAccessorTest
 */
final class PrivatesAccessor
{
    /**
     * @template T of object
     *
     * @param class-string<T> $valueClassName
     * @return T
     */
    public function getPrivatePropertyOfClass(object $object, string $propertyName, string $valueClassName) : object
    {
        $value = $this->getPrivateProperty($object, $propertyName);
        if ($value instanceof $valueClassName) {
            return $value;
        }
        $errorMessage = \sprintf('The type "%s" is required, but "%s" type given', $valueClassName, \get_class($value));
        throw new InvalidPrivatePropertyTypeException($errorMessage);
    }
    /**
     * @return mixed
     */
    public function getPrivateProperty(object $object, string $propertyName)
    {
        $propertyReflection = $this->resolvePropertyReflection($object, $propertyName);
        $propertyReflection->setAccessible(\true);
        return $propertyReflection->getValue($object);
    }
    /**
     * @template T of object
     *
     * @param class-string<T> $valueClassName
     * @param mixed $value
     */
    public function setPrivatePropertyOfClass(object $object, string $propertyName, $value, string $valueClassName) : void
    {
        if ($value instanceof $valueClassName) {
            $this->setPrivateProperty($object, $propertyName, $value);
            return;
        }
        $errorMessage = \sprintf('The type "%s" is required, but "%s" type given', $valueClassName, \get_class($value));
        throw new InvalidPrivatePropertyTypeException($errorMessage);
    }
    /**
     * @param mixed $value
     */
    public function setPrivateProperty(object $object, string $propertyName, $value) : void
    {
        $propertyReflection = $this->resolvePropertyReflection($object, $propertyName);
        $propertyReflection->setAccessible(\true);
        $propertyReflection->setValue($object, $value);
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
