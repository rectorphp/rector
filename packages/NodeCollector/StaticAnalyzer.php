<?php

declare(strict_types=1);

namespace Rector\NodeCollector;

use Nette\Utils\Strings;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\NodeCollector\NodeCollector\NodeRepository;

final class StaticAnalyzer
{
    public function __construct(
        private NodeRepository $nodeRepository,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    public function isStaticMethod(string $methodName, string $className): bool
    {
        $classMethod = $this->nodeRepository->findClassMethod($className, $methodName);
        if ($classMethod !== null) {
            return $classMethod->isStatic();
        }

        // could be static in doc type magic
        // @see https://regex101.com/r/tlvfTB/1
        if (! $this->reflectionProvider->hasClass($className)) {
            return false;
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        if ($this->hasStaticAnnotation($methodName, $classReflection)) {
            return true;
        }

        // probably magic method → we don't know
        if (! $classReflection->hasNativeMethod($methodName)) {
            return false;
        }

        $methodReflection = $classReflection->getNativeMethod($methodName);
        return $methodReflection->isStatic();
    }

    private function hasStaticAnnotation(string $methodName, ClassReflection $classReflection): bool
    {
        $resolvedPhpDocBlock = $classReflection->getResolvedPhpDoc();
        if (! $resolvedPhpDocBlock instanceof ResolvedPhpDocBlock) {
            return false;
        }

        return (bool) Strings::match(
            $resolvedPhpDocBlock->getPhpDocString(),
            '#@method\s*static\s*(.*?)\b' . $methodName . '\b#'
        );
    }
}
