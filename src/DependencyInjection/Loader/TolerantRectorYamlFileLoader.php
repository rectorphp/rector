<?php

declare(strict_types=1);

namespace Rector\Core\DependencyInjection\Loader;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\DependencyInjection\Collector\RectorServiceArgumentCollector;
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
     * @var RectorServiceArgumentCollector
     */
    private $rectorServiceArgumentCollector;

    public function __construct(
        ContainerBuilder $containerBuilder,
        FileLocatorInterface $fileLocator,
        RectorServiceArgumentCollector $rectorServiceArgumentCollector
    ) {
        $this->parameterInImportResolver = new ParameterInImportResolver();
        $this->classExistenceValidator = new ClassExistenceValidator();
        $this->rectorServiceArgumentCollector = $rectorServiceArgumentCollector;

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
        foreach ($configuration['services'] as $className => $service) {
            if (! is_string($className)) {
                continue;
            }

            // skip non-rectors
            if (! is_a($className, RectorInterface::class, true)) {
                continue;
            }

            if (! is_array($service)) {
                continue;
            }

            $this->rectorServiceArgumentCollector->collectFromServiceAndRectorClass($service, $className);
        }
    }
}
