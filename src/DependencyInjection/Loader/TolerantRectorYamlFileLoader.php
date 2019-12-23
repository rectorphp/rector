<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\Loader;

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

    public function __construct(ContainerBuilder $containerBuilder, FileLocatorInterface $fileLocator)
    {
        $this->parameterInImportResolver = new ParameterInImportResolver();
        $this->classExistenceValidator = new ClassExistenceValidator();

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

        // @todo: merge service arguments as well
        foreach ($configuration['services'] ?? [] as $service) {
            if (! isset($service['arguments'])) {
                continue;
            }

//            dump($service['arguments']);
        }

        $this->classExistenceValidator->ensureClassesAreValid($configuration, $file);

//        $configuration = $this->rectorServiceParametersShifter->process($configuration, $file);

        return $this->parameterInImportResolver->process($configuration);
    }
}
