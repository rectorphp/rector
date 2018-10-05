<?php declare(strict_types=1);

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
     * @var RectorServiceParametersShifter
     */
    private $rectorServiceParametersShifter;

    public function __construct(ContainerBuilder $containerBuilder, FileLocatorInterface $fileLocator)
    {
        $this->parameterInImportResolver = new ParameterInImportResolver();
        $this->rectorServiceParametersShifter = new RectorServiceParametersShifter();

        parent::__construct($containerBuilder, $fileLocator);
    }

    /**
     * @param string $file
     * @return array|mixed|mixed[]
     */
    protected function loadFile($file)
    {
        /** @var mixed[]|null $configuration */
        $configuration = parent::loadFile($file);
        if ($configuration === null) {
            return [];
        }

        $configuration = $this->rectorServiceParametersShifter->process($configuration);

        return $this->parameterInImportResolver->process($configuration);
    }
}
