<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig;

use Rector\SymfonyPhpConfig\Reflection\ArgumentAndParameterFactory;
use ReflectionClass;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use Symfony\Component\DependencyInjection\Loader\Configurator\InlineServiceConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;

function inline_argument_object(object $object, ServicesConfigurator $servicesConfigurator): ReferenceConfigurator
{
    $reflectionClass = new ReflectionClass($object);

    $className = $reflectionClass->getName();
    $propertyValues = resolve_property_values($reflectionClass, $object);
    $argumentValues = resolve_argument_values($reflectionClass, $object);

    // create fake factory with private accessor, as properties are different
    // @see https://symfony.com/doc/current/service_container/factories.html#passing-arguments-to-the-factory-method
    $servicesConfigurator->set(ArgumentAndParameterFactory::class);

    $servicesConfigurator->set($className)
        ->factory([ref(ArgumentAndParameterFactory::class), 'create'])
        ->args([$className, $argumentValues, $propertyValues]);

    return ref($className);
}

function inline_value_object(object $object): InlineServiceConfigurator
{
    $reflectionClass = new ReflectionClass($object);

    $className = $reflectionClass->getName();
    $argumentValues = resolve_argument_values($reflectionClass, $object);

    if (function_exists('Symfony\Component\DependencyInjection\Loader\Configurator\inline_service')) {
        // Symfony 5.1+
        $inlineServiceConfigurator = inline_service($className);
    } else {
        // Symfony 5.0-
        $inlineServiceConfigurator = inline($className);
    }

    if ($argumentValues !== []) {
        $inlineServiceConfigurator->args($argumentValues);
    }

    return $inlineServiceConfigurator;
}

/**
 * @param object[] $objects
 * @return InlineServiceConfigurator[]
 */
function inline_value_objects(array $objects): array
{
    $inlineServices = [];
    foreach ($objects as $object) {
        $inlineServices[] = inline_value_object($object);
    }

    return $inlineServices;
}

/**
 * @return mixed[]
 */
function resolve_argument_values(ReflectionClass $reflectionClass, object $object): array
{
    $argumentValues = [];

    $constructorMethodReflection = $reflectionClass->getConstructor();
    if ($constructorMethodReflection === null) {
        // value object without constructor
        return [];
    }

    foreach ($constructorMethodReflection->getParameters() as $reflectionParameter) {
        $parameterName = $reflectionParameter->getName();
        $propertyReflection = $reflectionClass->getProperty($parameterName);
        $propertyReflection->setAccessible(true);

        $resolvedValue = $propertyReflection->getValue($object);

        if (is_array($resolvedValue)) {
            foreach ($resolvedValue as $key => $value) {
                if (is_object($value)) {
                    $resolvedValue[$key] = inline_value_object($value);
                }
            }
        }

        $argumentValues[] = is_object($resolvedValue) ? inline_value_object($resolvedValue) : $resolvedValue;
    }

    return $argumentValues;
}

/**
 * @return array<string, mixed>
 */
function resolve_property_values(ReflectionClass $reflectionClass, object $object): array
{
    $propertyValues = [];

    foreach ($reflectionClass->getProperties() as $reflectionProperty) {
        $parameterName = $reflectionProperty->getName();
        $reflectionProperty->setAccessible(true);

        $propertyValues[$parameterName] = $reflectionProperty->getValue($object);
    }

    return $propertyValues;
}
