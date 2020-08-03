<?php

declare(strict_types=1);

namespace Rector\Core\Reflection;

use Rector\Core\Exception\ShouldNotHappenException;
use ReflectionClass;

final class ConstantNameFromValueResolver
{
    public function resolveFromValueAndClass(string $constantValue, string $class): string
    {
        $reflectionClass = new ReflectionClass($class);
        foreach ($reflectionClass->getConstants() as $name => $value) {
            if ($value === $constantValue) {
                return $name;
            }
        }

        $message = sprintf(
            'Constant value "%s" for class "%s" could not be resolved. Make sure you use constants references there, not string values',
            $constantValue,
            $class
        );
        throw new ShouldNotHappenException($message);
    }
}
