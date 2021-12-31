<?php

declare (strict_types=1);
namespace RectorPrefix20211231\Symplify\SymplifyKernel\HttpKernel;

use RectorPrefix20211231\Symfony\Component\DependencyInjection\Container;
use RectorPrefix20211231\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix20211231\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
use RectorPrefix20211231\Symplify\SymplifyKernel\Config\Loader\ParameterMergingLoaderFactory;
use RectorPrefix20211231\Symplify\SymplifyKernel\ContainerBuilderFactory;
use RectorPrefix20211231\Symplify\SymplifyKernel\Contract\LightKernelInterface;
use RectorPrefix20211231\Symplify\SymplifyKernel\Exception\ShouldNotHappenException;
use RectorPrefix20211231\Symplify\SymplifyKernel\ValueObject\SymplifyKernelConfig;
/**
 * @api
 */
abstract class AbstractSymplifyKernel implements \RectorPrefix20211231\Symplify\SymplifyKernel\Contract\LightKernelInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container|null
     */
    private $container = null;
    /**
     * @param string[] $configFiles
     */
    public function create(array $extensions, array $compilerPasses, array $configFiles) : \RectorPrefix20211231\Symfony\Component\DependencyInjection\ContainerInterface
    {
        $containerBuilderFactory = new \RectorPrefix20211231\Symplify\SymplifyKernel\ContainerBuilderFactory(new \RectorPrefix20211231\Symplify\SymplifyKernel\Config\Loader\ParameterMergingLoaderFactory());
        $compilerPasses[] = new \RectorPrefix20211231\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass();
        $configFiles[] = \RectorPrefix20211231\Symplify\SymplifyKernel\ValueObject\SymplifyKernelConfig::FILE_PATH;
        $containerBuilder = $containerBuilderFactory->create($extensions, $compilerPasses, $configFiles);
        $containerBuilder->compile();
        $this->container = $containerBuilder;
        return $containerBuilder;
    }
    public function getContainer() : \RectorPrefix20211231\Psr\Container\ContainerInterface
    {
        if (!$this->container instanceof \RectorPrefix20211231\Symfony\Component\DependencyInjection\Container) {
            throw new \RectorPrefix20211231\Symplify\SymplifyKernel\Exception\ShouldNotHappenException();
        }
        return $this->container;
    }
}
