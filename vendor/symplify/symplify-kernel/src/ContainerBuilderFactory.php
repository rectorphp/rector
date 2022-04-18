<?php

declare (strict_types=1);
namespace RectorPrefix20220418\Symplify\SymplifyKernel;

use RectorPrefix20220418\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use RectorPrefix20220418\Symplify\SymplifyKernel\Contract\Config\LoaderFactoryInterface;
use RectorPrefix20220418\Symplify\SymplifyKernel\DependencyInjection\LoadExtensionConfigsCompilerPass;
use RectorPrefix20220418\Webmozart\Assert\Assert;
/**
 * @see \Symplify\SymplifyKernel\Tests\ContainerBuilderFactory\ContainerBuilderFactoryTest
 */
final class ContainerBuilderFactory
{
    /**
     * @var \Symplify\SymplifyKernel\Contract\Config\LoaderFactoryInterface
     */
    private $loaderFactory;
    public function __construct(\RectorPrefix20220418\Symplify\SymplifyKernel\Contract\Config\LoaderFactoryInterface $loaderFactory)
    {
        $this->loaderFactory = $loaderFactory;
    }
    /**
     * @param string[] $configFiles
     * @param CompilerPassInterface[] $compilerPasses
     * @param ExtensionInterface[] $extensions
     */
    public function create(array $configFiles, array $compilerPasses, array $extensions) : \RectorPrefix20220418\Symfony\Component\DependencyInjection\ContainerBuilder
    {
        \RectorPrefix20220418\Webmozart\Assert\Assert::allIsAOf($extensions, \RectorPrefix20220418\Symfony\Component\DependencyInjection\Extension\ExtensionInterface::class);
        \RectorPrefix20220418\Webmozart\Assert\Assert::allIsAOf($compilerPasses, \RectorPrefix20220418\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface::class);
        \RectorPrefix20220418\Webmozart\Assert\Assert::allString($configFiles);
        \RectorPrefix20220418\Webmozart\Assert\Assert::allFile($configFiles);
        $containerBuilder = new \RectorPrefix20220418\Symfony\Component\DependencyInjection\ContainerBuilder();
        $this->registerExtensions($containerBuilder, $extensions);
        $this->registerConfigFiles($containerBuilder, $configFiles);
        $this->registerCompilerPasses($containerBuilder, $compilerPasses);
        // this calls load() method in every extensions
        // ensure these extensions are implicitly loaded
        $compilerPassConfig = $containerBuilder->getCompilerPassConfig();
        $compilerPassConfig->setMergePass(new \RectorPrefix20220418\Symplify\SymplifyKernel\DependencyInjection\LoadExtensionConfigsCompilerPass());
        return $containerBuilder;
    }
    /**
     * @param ExtensionInterface[] $extensions
     */
    private function registerExtensions(\RectorPrefix20220418\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, array $extensions) : void
    {
        foreach ($extensions as $extension) {
            $containerBuilder->registerExtension($extension);
        }
    }
    /**
     * @param CompilerPassInterface[] $compilerPasses
     */
    private function registerCompilerPasses(\RectorPrefix20220418\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, array $compilerPasses) : void
    {
        foreach ($compilerPasses as $compilerPass) {
            $containerBuilder->addCompilerPass($compilerPass);
        }
    }
    /**
     * @param string[] $configFiles
     */
    private function registerConfigFiles(\RectorPrefix20220418\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, array $configFiles) : void
    {
        $delegatingLoader = $this->loaderFactory->create($containerBuilder, \getcwd());
        foreach ($configFiles as $configFile) {
            $delegatingLoader->load($configFile);
        }
    }
}
