<?php

declare(strict_types=1);

namespace Rector\Core\Reflection;

use ReflectionMethod;

final class ClassMethodReflectionFactory
{
    public function createReflectionMethodIfExists(string $class, string $method): ?ReflectionMethod
    {
        if (! method_exists($class, $method)) {
            return null;
        }

        return new ReflectionMethod($class, $method);
    }
}
