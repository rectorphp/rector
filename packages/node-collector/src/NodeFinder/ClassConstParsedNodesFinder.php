<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeFinder;

use Rector\SOLID\Analyzer\ClassConstantFetchAnalyzer;

final class ClassConstParsedNodesFinder
{
    /**
     * @var ClassConstantFetchAnalyzer
     */
    private $classConstantFetchAnalyzer;

    public function __construct(ClassConstantFetchAnalyzer $classConstantFetchAnalyzer)
    {
        $this->classConstantFetchAnalyzer = $classConstantFetchAnalyzer;
    }

    /**
     * @return string[]
     */
    public function findDirectClassConstantFetches(string $desiredClassName, string $desiredConstantName): array
    {
        $classConstantFetchByClassAndName = $this->classConstantFetchAnalyzer->provideClassConstantFetchByClassAndName();

        return $classConstantFetchByClassAndName[$desiredClassName][$desiredConstantName] ?? [];
    }

    /**
     * @return string[]
     */
    public function findIndirectClassConstantFetches(string $desiredClassName, string $desiredConstantName): array
    {
        $classConstantFetchByClassAndName = $this->classConstantFetchAnalyzer->provideClassConstantFetchByClassAndName();
        foreach ($classConstantFetchByClassAndName as $className => $classesByConstantName) {
            if (! isset($classesByConstantName[$desiredConstantName])) {
                continue;
            }

            // include child usages
            if (! is_a($desiredClassName, $className, true)) {
                continue;
            }

            if ($desiredClassName === $className) {
                continue;
            }

            return $classesByConstantName[$desiredConstantName];
        }

        return [];
    }
}
