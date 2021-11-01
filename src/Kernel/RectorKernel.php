<?php

declare(strict_types=1);

namespace Rector\Core\Kernel;

use Rector\Core\Config\Loader\ConfigureCallMergingLoaderFactory;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector;
use Rector\Core\DependencyInjection\CompilerPass\MakeRectorsPublicCompilerPass;
use Rector\Core\DependencyInjection\CompilerPass\MergeImportedRectorConfigureCallValuesCompilerPass;
use Rector\Core\DependencyInjection\CompilerPass\RemoveSkippedRectorsCompilerPass;
use Rector\Core\DependencyInjection\CompilerPass\VerifyRectorServiceExistsCompilerPass;
use Rector\Core\Exception\ShouldNotHappenException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symplify\Astral\ValueObject\AstralConfig;
use Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonManipulatorConfig;
use Symplify\ConsoleColorDiff\ValueObject\ConsoleColorDiffConfig;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutowireInterfacesCompilerPass;
use Symplify\SimplePhpDocParser\ValueObject\SimplePhpDocParserConfig;
use Symplify\Skipper\ValueObject\SkipperConfig;
use Symplify\SymfonyContainerBuilder\ContainerBuilderFactory;
use Symplify\SymplifyKernel\Contract\LightKernelInterface;

final class RectorKernel implements LightKernelInterface
{
    private ConfigureCallValuesCollector $configureCallValuesCollector;

    private ContainerInterface|null $container = null;

    public function __construct()
    {
        $this->configureCallValuesCollector = new ConfigureCallValuesCollector();
    }

    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles): ContainerInterface
    {
        $defaultConfigFiles = $this->createDefaultConfigFiles();
        $configFiles = array_merge($defaultConfigFiles, $configFiles);

        $compilerPasses = $this->createCompilerPasses();

        $configureCallMergingLoaderFactory = new ConfigureCallMergingLoaderFactory($this->configureCallValuesCollector);

        $containerBuilderFactory = new ContainerBuilderFactory($configureCallMergingLoaderFactory);

        $containerBuilder = $containerBuilderFactory->create([], $compilerPasses, $configFiles);
        // @see https://symfony.com/blog/new-in-symfony-4-4-dependency-injection-improvements-part-1
        $containerBuilder->setParameter('container.dumper.inline_factories', true);
        // to fix reincluding files again
        $containerBuilder->setParameter('container.dumper.inline_class_loader', false);

        $containerBuilder->compile();

        $this->container = $containerBuilder;

        return $containerBuilder;
    }

    public function getContainer(): ContainerInterface
    {
        if ($this->container === null) {
            throw new ShouldNotHappenException();
        }

        return $this->container;
    }

    /**
     * @return CompilerPassInterface[]
     */
    private function createCompilerPasses(): array
    {
        $compilerPasses = [];

        // must run before AutowireArrayParameterCompilerPass, as the autowired array cannot contain removed services
        $compilerPasses[] = new RemoveSkippedRectorsCompilerPass();

        // autowire Rectors by default (mainly for tests)
        $compilerPasses[] = new AutowireInterfacesCompilerPass([RectorInterface::class]);
        $compilerPasses[] = new MakeRectorsPublicCompilerPass();

        // add all merged arguments of Rector services
        $compilerPasses[] = new MergeImportedRectorConfigureCallValuesCompilerPass($this->configureCallValuesCollector);
        $compilerPasses[] = new VerifyRectorServiceExistsCompilerPass();

        $compilerPasses[] = new AutowireArrayParameterCompilerPass();

        return $compilerPasses;
    }

    /**
     * @return string[]
     */
    private function createDefaultConfigFiles(): array
    {
        $configFiles = [];

        $configFiles[] = __DIR__ . '/../../config/config.php';
        $configFiles[] = AstralConfig::FILE_PATH;
        $configFiles[] = ComposerJsonManipulatorConfig::FILE_PATH;
        $configFiles[] = ConsoleColorDiffConfig::FILE_PATH;
        $configFiles[] = SimplePhpDocParserConfig::FILE_PATH;
        $configFiles[] = SkipperConfig::FILE_PATH;

        return $configFiles;
    }
}
