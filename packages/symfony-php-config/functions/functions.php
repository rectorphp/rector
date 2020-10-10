<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig;

use Rector\SymfonyPhpConfig\Exception\ValueObjectException;
use Rector\SymfonyPhpConfig\Reflection\ArgumentAndParameterFactory;
use ReflectionClass;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use Symfony\Component\DependencyInjection\Loader\Configurator\InlineServiceConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;

function inline_single_object(object $object, ServicesConfigurator $servicesConfigurator): ReferenceConfigurator
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

    // Symfony 5.1+
    if (function_exists('Symfony\Component\DependencyInjection\Loader\Configurator\inline_service')) {
        return inline_service($className)
            ->args($argumentValues);
    }

    // Symfony 5.0-
    return inline($className)
        ->args($argumentValues);
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
        $message = sprintf(
            'Constructor for "%s" was not found. Be sure to use only value objects',
            $reflectionClass->getName()
        );
        throw new ValueObjectException($message);
    }

    foreach ($constructorMethodReflection->getParameters() as $reflectionParameter) {
        $parameterName = $reflectionParameter->getName();
        $propertyReflection = $reflectionClass->getProperty($parameterName);
        $propertyReflection->setAccessible(true);

        $argumentValues[] = $propertyReflection->getValue($object);
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
