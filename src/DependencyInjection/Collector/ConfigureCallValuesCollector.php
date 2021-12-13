<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection\Collector;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use ReflectionClass;
use RectorPrefix20211213\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20211213\Symfony\Component\DependencyInjection\Definition;
use RectorPrefix20211213\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use RectorPrefix20211213\Symplify\PackageBuilder\Yaml\ParametersMerger;
final class ConfigureCallValuesCollector
{
    /**
     * @var mixed[]
     */
    private $configureCallValuesByRectorClass = [];
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Yaml\ParametersMerger
     */
    private $parametersMerger;
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct()
    {
        $this->parametersMerger = new \RectorPrefix20211213\Symplify\PackageBuilder\Yaml\ParametersMerger();
        $symfonyStyleFactory = new \RectorPrefix20211213\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory();
        $this->symfonyStyle = $symfonyStyleFactory->create();
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
    public function collectFromServiceAndClassName(string $className, \RectorPrefix20211213\Symfony\Component\DependencyInjection\Definition $definition) : void
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
            // is nested or unnested value?
            if (\is_array($configureValue) && \count($configureValue) === 1) {
                \reset($configureValue);
                $firstKey = \key($configureValue);
                if (\is_string($firstKey) && \is_array($configureValue[$firstKey])) {
                    // has class some public constants?
                    // fixes bug when 1 item is unwrapped and treated as constant key, without rule having public constant
                    $classReflection = new \ReflectionClass($rectorClass);
                    $reflectionClassConstants = $classReflection->getReflectionConstants();
                    foreach ($reflectionClassConstants as $reflectionClassConstant) {
                        if (!$reflectionClassConstant->isPublic()) {
                            continue;
                        }
                        $constantValue = $reflectionClassConstant->getValue();
                        $constantName = $reflectionClassConstant->getName();
                        if ($constantValue === $firstKey) {
                            if (\strpos((string) $reflectionClassConstant->getDocComment(), '@deprecated') === \false) {
                                continue;
                            }
                            $warningMessage = \sprintf('The constant for "%s::%s" is deprecated.%sUse "->configure()" directly instead.', $rectorClass, $constantName, \PHP_EOL);
                            $this->symfonyStyle->warning($warningMessage);
                            $configureValue = $configureValue[$firstKey];
                            break;
                        }
                    }
                }
            }
            if (!isset($this->configureCallValuesByRectorClass[$rectorClass])) {
                $this->configureCallValuesByRectorClass[$rectorClass] = $configureValue;
            } else {
                $mergedParameters = $this->parametersMerger->merge($this->configureCallValuesByRectorClass[$rectorClass], $configureValue);
                $this->configureCallValuesByRectorClass[$rectorClass] = $mergedParameters;
            }
        }
    }
}
