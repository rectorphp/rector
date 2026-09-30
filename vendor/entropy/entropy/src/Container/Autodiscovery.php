<?php

declare (strict_types=1);
namespace RectorPrefix202609\Entropy\Container;

use RectorPrefix202609\Entropy\Attribute\RelatedTest;
use RectorPrefix202609\Entropy\FileSystem\FileFinder;
use RectorPrefix202609\Entropy\Reflection\ClassNameResolver;
use RectorPrefix202609\Entropy\Tests\Container\Autodiscovery\AutodiscoveryTest;
use RectorPrefix202609\Entropy\Validation\Assert;
use ReflectionClass;
use Throwable;
/**
 * Registers project classes to services automatically
 */
final class Autodiscovery
{
    /**
     * @return array<class-string>
     */
    public function autodiscoverDirectory(string $directory): array
    {
        $phpFiles = FileFinder::findPhpFiles($directory);
        return $this->resolveClassNames($phpFiles);
    }
    private function shouldSkipClass(string $className): bool
    {
        // @todo exclude classes with ValueObject, DTO, Enum, Exception in their namespace
        // those are not services
        $reflectionClass = new ReflectionClass($className);
        // interface cannot be registered as a service
        if ($reflectionClass->isInterface()) {
            return \true;
        }
        if ($reflectionClass->isSubclassOf(Throwable::class)) {
            return \true;
        }
        // isEnum() exists on PHP 8.1+; on older versions there are no enums to skip
        if (\PHP_VERSION_ID >= 80100 && (method_exists($reflectionClass, 'isEnum') ? $reflectionClass->isEnum() : \false)) {
            return \true;
        }
        // no parent class/interface, nothing to register
        return $reflectionClass->getParentClass() === \false && $reflectionClass->getInterfaceNames() === [];
    }
    /**
     * @param string[] $phpFiles
     *
     * @return array<class-string>
     */
    private function resolveClassNames(array $phpFiles): array
    {
        Assert::allString($phpFiles);
        $classNames = [];
        foreach ($phpFiles as $phpFile) {
            $className = ClassNameResolver::resolveFromFilePath($phpFile);
            if ($className === null) {
                continue;
            }
            if ($this->shouldSkipClass($className)) {
                continue;
            }
            $classNames[] = $className;
        }
        return $classNames;
    }
}
