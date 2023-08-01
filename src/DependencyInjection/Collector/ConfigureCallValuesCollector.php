<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection\Collector;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Util\ArrayParametersMerger;
use RectorPrefix202308\Symfony\Component\DependencyInjection\Definition;
final class ConfigureCallValuesCollector
{
    /**
     * @var array<string, mixed[]>
     */
    private $configureCallValuesByRectorClass = [];
    /**
     * @readonly
     * @var \Rector\Core\Util\ArrayParametersMerger
     */
    private $arrayParametersMerger;
    public function __construct()
    {
        $this->arrayParametersMerger = new ArrayParametersMerger();
    }
    /**
     * @return mixed[]
     */
    public function getConfigureCallValues(string $rectorClass) : array
    {
        return $this->configureCallValuesByRectorClass[$rectorClass] ?? [];
    }
    /**
     * @param class-string<ConfigurableRectorInterface> $className
     */
    public function collectFromServiceAndClassName(string $className, Definition $definition) : void
    {
        foreach ($definition->getMethodCalls() as $methodCall) {
            if ($methodCall[0] !== 'configure') {
                continue;
            }
            $this->addConfigureCallValues($className, $methodCall[1]);
        }
    }
    /**
     * @param class-string<ConfigurableRectorInterface> $rectorClass
     * @param mixed[] $configureValues
     */
    private function addConfigureCallValues(string $rectorClass, array $configureValues) : void
    {
        foreach ($configureValues as $configureValue) {
            if (!isset($this->configureCallValuesByRectorClass[$rectorClass])) {
                $this->configureCallValuesByRectorClass[$rectorClass] = $configureValue;
            } else {
                $mergedParameters = $this->arrayParametersMerger->merge($this->configureCallValuesByRectorClass[$rectorClass], $configureValue);
                $this->configureCallValuesByRectorClass[$rectorClass] = $mergedParameters;
            }
        }
    }
}
