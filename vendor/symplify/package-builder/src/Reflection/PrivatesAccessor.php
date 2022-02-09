<?php

declare (strict_types=1);
namespace RectorPrefix20220209\Symplify\PackageBuilder\Reflection;

use ReflectionProperty;
use RectorPrefix20220209\Symplify\PackageBuilder\Exception\InvalidPrivatePropertyTypeException;
use RectorPrefix20220209\Symplify\PackageBuilder\Exception\MissingPrivatePropertyException;
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
     * @return object
     * @param object $object
     */
    public function getPrivatePropertyOfClass($object, string $propertyName, string $valueClassName)
    {
        $value = $this->getPrivateProperty($object, $propertyName);
        if ($value instanceof $valueClassName) {
            return $value;
        }
        $errorMessage = \sprintf('The type "%s" is required, but "%s" type given', $valueClassName, \get_class($value));
        throw new \RectorPrefix20220209\Symplify\PackageBuilder\Exception\InvalidPrivatePropertyTypeException($errorMessage);
    }
    /**
     * @return mixed
     * @param object $object
     */
    public function getPrivateProperty($object, string $propertyName)
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
     * @param object $object
     */
    public function setPrivatePropertyOfClass($object, string $propertyName, $value, string $valueClassName) : void
    {
        if ($value instanceof $valueClassName) {
            $this->setPrivateProperty($object, $propertyName, $value);
            return;
        }
        $errorMessage = \sprintf('The type "%s" is required, but "%s" type given', $valueClassName, \get_class($value));
        throw new \RectorPrefix20220209\Symplify\PackageBuilder\Exception\InvalidPrivatePropertyTypeException($errorMessage);
    }
    /**
     * @param mixed $value
     * @param object $object
     */
    public function setPrivateProperty($object, string $propertyName, $value) : void
    {
        $propertyReflection = $this->resolvePropertyReflection($object, $propertyName);
        $propertyReflection->setAccessible(\true);
        $propertyReflection->setValue($object, $value);
    }
    /**
     * @param object $object
     */
    private function resolvePropertyReflection($object, string $propertyName) : \ReflectionProperty
    {
        if (\property_exists($object, $propertyName)) {
            return new \ReflectionProperty($object, $propertyName);
        }
        $parentClass = \get_parent_class($object);
        if ($parentClass !== \false) {
            return new \ReflectionProperty($parentClass, $propertyName);
        }
        $errorMessage = \sprintf('Property "$%s" was not found in "%s" class', $propertyName, \get_class($object));
        throw new \RectorPrefix20220209\Symplify\PackageBuilder\Exception\MissingPrivatePropertyException($errorMessage);
    }
}
