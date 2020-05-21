<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeFinder;

use Rector\NodeCollector\NodeCollector\ParsedClassConstFetchNodeCollector;

final class ClassConstParsedNodesFinder
{
    /**
     * @var ParsedClassConstFetchNodeCollector
     */
    private $parsedClassConstFetchNodeCollector;

    public function __construct(ParsedClassConstFetchNodeCollector $parsedClassConstFetchNodeCollector)
    {
        $this->parsedClassConstFetchNodeCollector = $parsedClassConstFetchNodeCollector;
    }

    /**
     * @return string[]
     */
    public function findDirectClassConstantFetches(string $desiredClassName, string $desiredConstantName): array
    {
        $classConstantFetchByClassAndName = $this->parsedClassConstFetchNodeCollector->getClassConstantFetchByClassAndName();

        return $classConstantFetchByClassAndName[$desiredClassName][$desiredConstantName] ?? [];
    }

    /**
     * @return string[]
     */
    public function findIndirectClassConstantFetches(string $desiredClassName, string $desiredConstantName): array
    {
        $classConstantFetchByClassAndName = $this->parsedClassConstFetchNodeCollector->getClassConstantFetchByClassAndName();

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
