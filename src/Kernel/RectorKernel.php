<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Kernel;

use RectorPrefix20220606\Rector\Core\Config\Loader\ConfigureCallMergingLoaderFactory;
use RectorPrefix20220606\Rector\Core\Contract\Rector\RectorInterface;
use RectorPrefix20220606\Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector;
use RectorPrefix20220606\Rector\Core\DependencyInjection\CompilerPass\MakeRectorsPublicCompilerPass;
use RectorPrefix20220606\Rector\Core\DependencyInjection\CompilerPass\MergeImportedRectorConfigureCallValuesCompilerPass;
use RectorPrefix20220606\Rector\Core\DependencyInjection\CompilerPass\RemoveSkippedRectorsCompilerPass;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use RectorPrefix20220606\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix20220606\Symplify\Astral\ValueObject\AstralConfig;
use RectorPrefix20220606\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
use RectorPrefix20220606\Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonManipulatorConfig;
use RectorPrefix20220606\Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutowireInterfacesCompilerPass;
use RectorPrefix20220606\Symplify\PackageBuilder\ValueObject\ConsoleColorDiffConfig;
use RectorPrefix20220606\Symplify\Skipper\ValueObject\SkipperConfig;
use RectorPrefix20220606\Symplify\SymplifyKernel\ContainerBuilderFactory;
use RectorPrefix20220606\Symplify\SymplifyKernel\Contract\LightKernelInterface;
final class RectorKernel implements LightKernelInterface
{
    /**
     * @readonly
     * @var \Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector
     */
    private $configureCallValuesCollector;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface|null
     */
    private $container = null;
    public function __construct()
    {
        $this->configureCallValuesCollector = new ConfigureCallValuesCollector();
    }
    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles) : \RectorPrefix20220606\Psr\Container\ContainerInterface
    {
        $defaultConfigFiles = $this->createDefaultConfigFiles();
        $configFiles = \array_merge($defaultConfigFiles, $configFiles);
        $compilerPasses = $this->createCompilerPasses();
        $configureCallMergingLoaderFactory = new ConfigureCallMergingLoaderFactory($this->configureCallValuesCollector);
        $containerBuilderFactory = new ContainerBuilderFactory($configureCallMergingLoaderFactory);
        $containerBuilder = $containerBuilderFactory->create($configFiles, $compilerPasses, []);
        // @see https://symfony.com/blog/new-in-symfony-4-4-dependency-injection-improvements-part-1
        $containerBuilder->setParameter('container.dumper.inline_factories', \true);
        // to fix reincluding files again
        $containerBuilder->setParameter('container.dumper.inline_class_loader', \false);
        $containerBuilder->compile();
        $this->container = $containerBuilder;
        return $containerBuilder;
    }
    public function getContainer() : \RectorPrefix20220606\Psr\Container\ContainerInterface
    {
        if ($this->container === null) {
            throw new ShouldNotHappenException();
        }
        return $this->container;
    }
    /**
     * @return CompilerPassInterface[]
     */
    private function createCompilerPasses() : array
    {
        return [
            // must run before AutowireArrayParameterCompilerPass, as the autowired array cannot contain removed services
            new RemoveSkippedRectorsCompilerPass(),
            // autowire Rectors by default (mainly for tests)
            new AutowireInterfacesCompilerPass([RectorInterface::class]),
            new MakeRectorsPublicCompilerPass(),
            // add all merged arguments of Rector services
            new MergeImportedRectorConfigureCallValuesCompilerPass($this->configureCallValuesCollector),
            new AutowireArrayParameterCompilerPass(),
        ];
    }
    /**
     * @return string[]
     */
    private function createDefaultConfigFiles() : array
    {
        return [__DIR__ . '/../../config/config.php', AstralConfig::FILE_PATH, ComposerJsonManipulatorConfig::FILE_PATH, SkipperConfig::FILE_PATH, ConsoleColorDiffConfig::FILE_PATH];
    }
}
