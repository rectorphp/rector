<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\Collector;

use Nette\Utils\Strings;
use Rector\Contract\Rector\RectorInterface;
use Symplify\PackageBuilder\Yaml\ParametersMerger;

final class RectorServiceArgumentCollector
{
    /**
     * @var mixed[]
     */
    private $cachedRectorServiceKeyArguments = [];

    /**
     * @var ParametersMerger
     */
    private $parametersMerger;

    public function __construct()
    {
        $this->parametersMerger = new ParametersMerger();
    }

    public function getClassArgumentValue(string $serviceClass): array
    {
        return $this->cachedRectorServiceKeyArguments[$serviceClass] ?? [];
    }

    public function collectFromServiceAndRectorClass(array $service, string $className): void
    {
        if (! is_a($className, RectorInterface::class, true)) {
            return;
        }

        foreach ($service as $argumentName => $argumentValue) {
            // is that argument?
            if (! Strings::startsWith($argumentName, '$')) {
                continue;
            }

            $this->addArgumentValue($className, $argumentName, $argumentValue);
        }
    }

    private function addArgumentValue(string $serviceClassName, string $argumentName, $argumentValue): void
    {
        if (! isset($this->cachedRectorServiceKeyArguments[$serviceClassName][$argumentName])) {
            $this->cachedRectorServiceKeyArguments[$serviceClassName][$argumentName] = $argumentValue;
        } else {
            $mergedParameters = $this->parametersMerger->merge(
                $this->cachedRectorServiceKeyArguments[$serviceClassName][$argumentName],
                $argumentValue
            );

            $this->cachedRectorServiceKeyArguments[$serviceClassName][$argumentName] = $mergedParameters;
        }
    }
}
