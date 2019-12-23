<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\Loader;

use Nette\Utils\Strings;
use Rector\Contract\Rector\RectorInterface;
use Rector\DependencyInjection\Collector\CachedServiceArgumentCollector;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symplify\PackageBuilder\Yaml\FileLoader\AbstractParameterMergingYamlFileLoader;
use Symplify\PackageBuilder\Yaml\ParameterInImportResolver;

final class TolerantRectorYamlFileLoader extends AbstractParameterMergingYamlFileLoader
{
    /**
     * @var ParameterInImportResolver
     */
    private $parameterInImportResolver;

    /**
     * @var ClassExistenceValidator
     */
    private $classExistenceValidator;

    /**
     * @var CachedServiceArgumentCollector
     */
    private $cachedServiceArgumentCollector;

    public function __construct(
        ContainerBuilder $containerBuilder,
        FileLocatorInterface $fileLocator,
        CachedServiceArgumentCollector $cachedServiceArgumentCollector
    ) {
        $this->parameterInImportResolver = new ParameterInImportResolver();
        $this->classExistenceValidator = new ClassExistenceValidator();
        $this->cachedServiceArgumentCollector = $cachedServiceArgumentCollector;

        parent::__construct($containerBuilder, $fileLocator);
    }

    /**
     * @param string $file
     * @return mixed|mixed[]
     */
    protected function loadFile($file)
    {
        /** @var mixed[]|null $configuration */
        $configuration = parent::loadFile($file);
        if ($configuration === null) {
            return [];
        }

        $this->collectRectorServiceArguments($configuration);

        $this->classExistenceValidator->ensureClassesAreValid($configuration, $file);

        return $this->parameterInImportResolver->process($configuration);
    }

    private function collectRectorServiceArguments(array $configuration): void
    {
        if (! isset($configuration['services'])) {
            return;
        }

        // merge service arguments as well
        foreach ($configuration['services'] as $possibleRectorClassName => $service) {
            if (! is_string($possibleRectorClassName)) {
                continue;
            }

            // skip non-rectors
            if (! is_a($possibleRectorClassName, RectorInterface::class, true)) {
                continue;
            }

            if (! is_array($service)) {
                continue;
            }

            $this->collectArgument($service, $possibleRectorClassName);
        }
    }

    private function collectArgument(array $service, string $rectorClassName): void
    {
        foreach ($service as $argumentName => $argumentValue) {
            if (! Strings::startsWith($argumentName, '$')) {
                continue;
            }

            $this->cachedServiceArgumentCollector->addArgumentValue(
                $rectorClassName,
                $argumentName,
                $argumentValue
            );
        }
    }
}
