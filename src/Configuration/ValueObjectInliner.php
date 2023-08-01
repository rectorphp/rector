<?php

declare (strict_types=1);
namespace Rector\Core\Configuration;

use ReflectionClass;
use ReflectionMethod;
use RectorPrefix202308\Symfony\Component\DependencyInjection\Definition;
use RectorPrefix202308\Symfony\Component\DependencyInjection\Loader\Configurator\InlineServiceConfigurator;
final class ValueObjectInliner
{
    /**
     * @param object|object[] $object
     * @return InlineServiceConfigurator|InlineServiceConfigurator[]
     */
    public static function inline($object)
    {
        if (\is_object($object)) {
            return self::inlineSingle($object);
        }
        return self::inlineMany($object);
    }
    /**
     * @param ReflectionClass<object> $reflectionClass
     * @return mixed[]
     */
    private static function resolveArgumentValues(ReflectionClass $reflectionClass, object $object) : array
    {
        $argumentValues = [];
        $constructorReflectionMethod = $reflectionClass->getConstructor();
        if (!$constructorReflectionMethod instanceof ReflectionMethod) {
            // value object without constructor
            return [];
        }
        foreach ($constructorReflectionMethod->getParameters() as $reflectionParameter) {
            $parameterName = $reflectionParameter->getName();
            $propertyReflection = $reflectionClass->getProperty($parameterName);
            $propertyReflection->setAccessible(\true);
            $resolvedValue = $propertyReflection->getValue($object);
            $resolvedValue = self::inlineNestedArrayObjects($resolvedValue);
            $argumentValues[] = \is_object($resolvedValue) ? self::inlineSingle($resolvedValue) : $resolvedValue;
        }
        return $argumentValues;
    }
    /**
     * @param object[] $objects
     * @return InlineServiceConfigurator[]
     */
    private static function inlineMany(array $objects) : array
    {
        $inlineServices = [];
        foreach ($objects as $object) {
            $inlineServices[] = self::inlineSingle($object);
        }
        return $inlineServices;
    }
    private static function inlineSingle(object $object) : InlineServiceConfigurator
    {
        $reflectionClass = new ReflectionClass($object);
        $className = $reflectionClass->getName();
        $argumentValues = self::resolveArgumentValues($reflectionClass, $object);
        $inlineServiceConfigurator = new InlineServiceConfigurator(new Definition($className));
        if ($argumentValues !== []) {
            $inlineServiceConfigurator->args($argumentValues);
        }
        return $inlineServiceConfigurator;
    }
    /**
     * @param mixed|mixed[] $resolvedValue
     * @return mixed|mixed[]
     */
    private static function inlineNestedArrayObjects($resolvedValue)
    {
        if (\is_array($resolvedValue)) {
            foreach ($resolvedValue as $key => $value) {
                if (\is_object($value)) {
                    $resolvedValue[$key] = self::inline($value);
                }
            }
        }
        return $resolvedValue;
    }
}
