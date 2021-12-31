<?php

declare (strict_types=1);
namespace Rector\Core\Kernel;

use Rector\Core\Config\Loader\ConfigureCallMergingLoaderFactory;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector;
use Rector\Core\DependencyInjection\CompilerPass\MakeRectorsPublicCompilerPass;
use Rector\Core\DependencyInjection\CompilerPass\MergeImportedRectorConfigureCallValuesCompilerPass;
use Rector\Core\DependencyInjection\CompilerPass\RemoveSkippedRectorsCompilerPass;
use Rector\Core\DependencyInjection\CompilerPass\VerifyRectorServiceExistsCompilerPass;
use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20211231\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use RectorPrefix20211231\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix20211231\Symplify\Astral\ValueObject\AstralConfig;
use RectorPrefix20211231\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
use RectorPrefix20211231\Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonManipulatorConfig;
use RectorPrefix20211231\Symplify\ConsoleColorDiff\ValueObject\ConsoleColorDiffConfig;
use RectorPrefix20211231\Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutowireInterfacesCompilerPass;
use RectorPrefix20211231\Symplify\SimplePhpDocParser\ValueObject\SimplePhpDocParserConfig;
use RectorPrefix20211231\Symplify\Skipper\ValueObject\SkipperConfig;
use RectorPrefix20211231\Symplify\SymplifyKernel\ContainerBuilderFactory;
use RectorPrefix20211231\Symplify\SymplifyKernel\Contract\LightKernelInterface;
final class RectorKernel implements \RectorPrefix20211231\Symplify\SymplifyKernel\Contract\LightKernelInterface
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
        $this->configureCallValuesCollector = new \Rector\Core\DependencyInjection\Collector\ConfigureCallValuesCollector();
    }
    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles) : \RectorPrefix20211231\Psr\Container\ContainerInterface
    {
        $defaultConfigFiles = $this->createDefaultConfigFiles();
        $configFiles = \array_merge($defaultConfigFiles, $configFiles);
        $compilerPasses = $this->createCompilerPasses();
        $configureCallMergingLoaderFactory = new \Rector\Core\Config\Loader\ConfigureCallMergingLoaderFactory($this->configureCallValuesCollector);
        $containerBuilderFactory = new \RectorPrefix20211231\Symplify\SymplifyKernel\ContainerBuilderFactory($configureCallMergingLoaderFactory);
        $containerBuilder = $containerBuilderFactory->create([], $compilerPasses, $configFiles);
        // @see https://symfony.com/blog/new-in-symfony-4-4-dependency-injection-improvements-part-1
        $containerBuilder->setParameter('container.dumper.inline_factories', \true);
        // to fix reincluding files again
        $containerBuilder->setParameter('container.dumper.inline_class_loader', \false);
        $containerBuilder->compile();
        $this->container = $containerBuilder;
        return $containerBuilder;
    }
    public function getContainer() : \RectorPrefix20211231\Psr\Container\ContainerInterface
    {
        if ($this->container === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $this->container;
    }
    /**
     * @return CompilerPassInterface[]
     */
    private function createCompilerPasses() : array
    {
        $compilerPasses = [];
        // must run before AutowireArrayParameterCompilerPass, as the autowired array cannot contain removed services
        $compilerPasses[] = new \Rector\Core\DependencyInjection\CompilerPass\RemoveSkippedRectorsCompilerPass();
        // autowire Rectors by default (mainly for tests)
        $compilerPasses[] = new \RectorPrefix20211231\Symplify\PackageBuilder\DependencyInjection\CompilerPass\AutowireInterfacesCompilerPass([\Rector\Core\Contract\Rector\RectorInterface::class]);
        $compilerPasses[] = new \Rector\Core\DependencyInjection\CompilerPass\MakeRectorsPublicCompilerPass();
        // add all merged arguments of Rector services
        $compilerPasses[] = new \Rector\Core\DependencyInjection\CompilerPass\MergeImportedRectorConfigureCallValuesCompilerPass($this->configureCallValuesCollector);
        $compilerPasses[] = new \Rector\Core\DependencyInjection\CompilerPass\VerifyRectorServiceExistsCompilerPass();
        $compilerPasses[] = new \RectorPrefix20211231\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass();
        return $compilerPasses;
    }
    /**
     * @return string[]
     */
    private function createDefaultConfigFiles() : array
    {
        $configFiles = [];
        $configFiles[] = __DIR__ . '/../../config/config.php';
        $configFiles[] = \RectorPrefix20211231\Symplify\Astral\ValueObject\AstralConfig::FILE_PATH;
        $configFiles[] = \RectorPrefix20211231\Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonManipulatorConfig::FILE_PATH;
        $configFiles[] = \RectorPrefix20211231\Symplify\ConsoleColorDiff\ValueObject\ConsoleColorDiffConfig::FILE_PATH;
        $configFiles[] = \RectorPrefix20211231\Symplify\SimplePhpDocParser\ValueObject\SimplePhpDocParserConfig::FILE_PATH;
        $configFiles[] = \RectorPrefix20211231\Symplify\Skipper\ValueObject\SkipperConfig::FILE_PATH;
        return $configFiles;
    }
}
