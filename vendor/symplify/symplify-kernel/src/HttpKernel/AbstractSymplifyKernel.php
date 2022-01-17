<?php

declare (strict_types=1);
namespace RectorPrefix20220117\Symplify\SymplifyKernel\HttpKernel;

use RectorPrefix20220117\Symfony\Component\DependencyInjection\Container;
use RectorPrefix20220117\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix20220117\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
use RectorPrefix20220117\Symplify\SymplifyKernel\Config\Loader\ParameterMergingLoaderFactory;
use RectorPrefix20220117\Symplify\SymplifyKernel\ContainerBuilderFactory;
use RectorPrefix20220117\Symplify\SymplifyKernel\Contract\LightKernelInterface;
use RectorPrefix20220117\Symplify\SymplifyKernel\Exception\ShouldNotHappenException;
use RectorPrefix20220117\Symplify\SymplifyKernel\ValueObject\SymplifyKernelConfig;
/**
 * @api
 */
abstract class AbstractSymplifyKernel implements \RectorPrefix20220117\Symplify\SymplifyKernel\Contract\LightKernelInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container|null
     */
    private $container = null;
    /**
     * @param string[] $configFiles
     */
    public function create(array $extensions, array $compilerPasses, array $configFiles) : \RectorPrefix20220117\Symfony\Component\DependencyInjection\ContainerInterface
    {
        $containerBuilderFactory = new \RectorPrefix20220117\Symplify\SymplifyKernel\ContainerBuilderFactory(new \RectorPrefix20220117\Symplify\SymplifyKernel\Config\Loader\ParameterMergingLoaderFactory());
        $compilerPasses[] = new \RectorPrefix20220117\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass();
        $configFiles[] = \RectorPrefix20220117\Symplify\SymplifyKernel\ValueObject\SymplifyKernelConfig::FILE_PATH;
        $containerBuilder = $containerBuilderFactory->create($extensions, $compilerPasses, $configFiles);
        $containerBuilder->compile();
        $this->container = $containerBuilder;
        return $containerBuilder;
    }
    public function getContainer() : \RectorPrefix20220117\Psr\Container\ContainerInterface
    {
        if (!$this->container instanceof \RectorPrefix20220117\Symfony\Component\DependencyInjection\Container) {
            throw new \RectorPrefix20220117\Symplify\SymplifyKernel\Exception\ShouldNotHappenException();
        }
        return $this->container;
    }
}
