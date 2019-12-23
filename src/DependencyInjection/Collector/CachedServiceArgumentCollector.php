<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\Collector;

use Symplify\PackageBuilder\Yaml\ParametersMerger;

final class CachedServiceArgumentCollector
{
    /**
     * @var ParametersMerger
     */
    private $parametersMerger;

    /**
     * @var mixed[]
     */
    private $cachedRectorServiceKeyArguments = [];

    public function __construct()
    {
        $this->parametersMerger = new ParametersMerger();
    }

    public function addArgumentValue(string $possibleRectorClassName, string $argumentName, $argumentValue): void
    {
        if (! isset($this->cachedRectorServiceKeyArguments[$possibleRectorClassName][$argumentName])) {
            $this->cachedRectorServiceKeyArguments[$possibleRectorClassName][$argumentName] = $argumentValue;
        } else {
            $mergedParameters = $this->parametersMerger->merge(
                $this->cachedRectorServiceKeyArguments[$possibleRectorClassName][$argumentName],
                $argumentValue
            );

            $this->cachedRectorServiceKeyArguments[$possibleRectorClassName][$argumentName] = $mergedParameters;
        }
    }

    public function getClassArgumentValue(string $serviceClass): array
    {
        return $this->cachedRectorServiceKeyArguments[$serviceClass] ?? [];
    }
}
