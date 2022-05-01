<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Symplify\PackageBuilder\Reflection;

use ReflectionClass;
use ReflectionMethod;
/**
 * @see \Symplify\PackageBuilder\Tests\Reflection\PrivatesCallerTest
 */
final class PrivatesCaller
{
    /**
     * @param mixed[] $arguments
     * @param object|string $object
     * @return mixed
     */
    public function callPrivateMethod($object, string $methodName, array $arguments)
    {
        if (\is_string($object)) {
            $reflectionClass = new \ReflectionClass($object);
            $object = $reflectionClass->newInstanceWithoutConstructor();
        }
        $methodReflection = $this->createAccessibleMethodReflection($object, $methodName);
        return $methodReflection->invokeArgs($object, $arguments);
    }
    /**
     * @param object|string $object
     * @param mixed $argument
     * @return mixed
     */
    public function callPrivateMethodWithReference($object, string $methodName, $argument)
    {
        if (\is_string($object)) {
            $reflectionClass = new \ReflectionClass($object);
            $object = $reflectionClass->newInstanceWithoutConstructor();
        }
        $methodReflection = $this->createAccessibleMethodReflection($object, $methodName);
        $methodReflection->invokeArgs($object, [&$argument]);
        return $argument;
    }
    private function createAccessibleMethodReflection(object $object, string $methodName) : \ReflectionMethod
    {
        $reflectionMethod = new \ReflectionMethod(\get_class($object), $methodName);
        $reflectionMethod->setAccessible(\true);
        return $reflectionMethod;
    }
}
