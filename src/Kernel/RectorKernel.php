<?php

declare (strict_types=1);
namespace Rector\Core\Kernel;

use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix202308\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix202308\Symfony\Component\DependencyInjection\ContainerInterface;
final class RectorKernel
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface|null
     */
    private $container = null;
    /**
     * @api used in tests
     */
    public function create() : ContainerInterface
    {
        return $this->createFromConfigs([]);
    }
    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles) : ContainerBuilder
    {
        $container = $this->buildContainer($configFiles);
        return $this->container = $container;
    }
    /**
     * @api used in tests
     */
    public function getContainer() : ContainerInterface
    {
        if (!$this->container instanceof ContainerInterface) {
            throw new ShouldNotHappenException();
        }
        return $this->container;
    }
    /**
     * @return string[]
     */
    private function createDefaultConfigFiles() : array
    {
        return [__DIR__ . '/../../config/config.php'];
    }
    /**
     * @param string[] $configFiles
     */
    private function buildContainer(array $configFiles) : ContainerBuilder
    {
        $defaultConfigFiles = $this->createDefaultConfigFiles();
        $configFiles = \array_merge($defaultConfigFiles, $configFiles);
        $containerBuilderBuilder = new \Rector\Core\Kernel\ContainerBuilderBuilder();
        return $this->container = $containerBuilderBuilder->build($configFiles);
    }
}
