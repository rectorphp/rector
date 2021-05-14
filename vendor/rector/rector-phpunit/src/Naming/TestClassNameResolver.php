<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Naming;

final class TestClassNameResolver
{
    /**
     * @return string[]
     */
    public function resolve(string $className) : array
    {
        $classNameParts = \explode('\\', $className);
        $possibleTestClassNames = [$className . 'Test'];
        $partCount = \count($classNameParts);
        for ($i = 0; $i < $partCount; ++$i) {
            $possibleClassNameParts = $classNameParts;
            \array_splice($possibleClassNameParts, $i, 0, ['Tests']);
            $possibleTestClassNames[] = \implode('\\', $possibleClassNameParts) . 'Test';
            $possibleClassNameParts = $classNameParts;
            \array_splice($possibleClassNameParts, $i, 0, ['Test']);
            $possibleTestClassNames[] = \implode('\\', $possibleClassNameParts) . 'Test';
        }
        return $possibleTestClassNames;
    }
}
