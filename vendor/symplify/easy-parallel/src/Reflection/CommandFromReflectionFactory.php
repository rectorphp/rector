<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Symplify\EasyParallel\Reflection;

use ReflectionClass;
use ReflectionMethod;
use RectorPrefix20220501\Symfony\Component\Console\Command\Command;
use RectorPrefix20220501\Symplify\EasyParallel\Exception\ParallelShouldNotHappenException;
final class CommandFromReflectionFactory
{
    /**
     * @param class-string<Command> $className
     */
    public function create(string $className) : \RectorPrefix20220501\Symfony\Component\Console\Command\Command
    {
        $commandReflectionClass = new \ReflectionClass($className);
        $command = $commandReflectionClass->newInstanceWithoutConstructor();
        $parentClassReflection = $commandReflectionClass->getParentClass();
        if (!$parentClassReflection instanceof \ReflectionClass) {
            throw new \RectorPrefix20220501\Symplify\EasyParallel\Exception\ParallelShouldNotHappenException();
        }
        $parentConstructorReflectionMethod = $parentClassReflection->getConstructor();
        if (!$parentConstructorReflectionMethod instanceof \ReflectionMethod) {
            throw new \RectorPrefix20220501\Symplify\EasyParallel\Exception\ParallelShouldNotHappenException();
        }
        $parentConstructorReflectionMethod->invoke($command);
        return $command;
    }
}
