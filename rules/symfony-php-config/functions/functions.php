<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig;

use ReflectionClass;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use Symfony\Component\DependencyInjection\Loader\Configurator\InlineServiceConfigurator;

/**
 * @param object[] $objects
 * @return InlineServiceConfigurator[]
 */
function inline_objects(array $objects): array
{
    foreach ($objects as $object) {
        $reflectionClass = new ReflectionClass($object);

        $className = $reflectionClass->getName();
        $argumentValues = resolve_argument_values($reflectionClass, $object);

        $inlineServices[] = inline_service($className)->args($argumentValues);
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
    foreach ($constructorMethodReflection->getParameters() as $constructorParameter) {
        $parameterName = $constructorParameter->getName();
        $propertyReflection = $reflectionClass->getProperty($parameterName);
        $propertyReflection->setAccessible(true);

        $argumentValues[] = $propertyReflection->getValue($object);
    }

    return $argumentValues;
}
