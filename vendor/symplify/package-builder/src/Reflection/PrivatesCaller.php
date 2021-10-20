<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Symplify\PackageBuilder\Reflection;

use ReflectionClass;
use ReflectionMethod;
use RectorPrefix20211020\Symplify\SymplifyKernel\Exception\ShouldNotHappenException;
/**
 * @see \Symplify\PackageBuilder\Tests\Reflection\PrivatesCallerTest
 */
final class PrivatesCaller
{
    /**
     * @param mixed[] $arguments
     * @return mixed
     * @param object|string $object
     */
    public function callPrivateMethod($object, string $methodName, array $arguments)
    {
        $this->ensureIsNotNull($object, __METHOD__);
        if (\is_string($object)) {
            $reflectionClass = new \ReflectionClass($object);
            $object = $reflectionClass->newInstanceWithoutConstructor();
        }
        $methodReflection = $this->createAccessibleMethodReflection($object, $methodName);
        return $methodReflection->invokeArgs($object, $arguments);
    }
    /**
     * @return mixed
     * @param object|string $object
     */
    public function callPrivateMethodWithReference($object, string $methodName, $argument)
    {
        $this->ensureIsNotNull($object, __METHOD__);
        if (\is_string($object)) {
            $reflectionClass = new \ReflectionClass($object);
            $object = $reflectionClass->newInstanceWithoutConstructor();
        }
        $methodReflection = $this->createAccessibleMethodReflection($object, $methodName);
        $methodReflection->invokeArgs($object, [&$argument]);
        return $argument;
    }
    /**
     * @param object $object
     */
    private function createAccessibleMethodReflection($object, string $methodName) : \ReflectionMethod
    {
        $reflectionMethod = new \ReflectionMethod(\get_class($object), $methodName);
        $reflectionMethod->setAccessible(\true);
        return $reflectionMethod;
    }
    /**
     * @param mixed $object
     */
    private function ensureIsNotNull($object, string $location) : void
    {
        if ($object !== null) {
            return;
        }
        $errorMessage = \sprintf('Value passed to "%s()" method cannot be null', $location);
        throw new \RectorPrefix20211020\Symplify\SymplifyKernel\Exception\ShouldNotHappenException($errorMessage);
    }
}
