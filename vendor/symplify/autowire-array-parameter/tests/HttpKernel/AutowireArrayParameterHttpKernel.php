<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\AutowireArrayParameter\Tests\HttpKernel;

use RectorPrefix20210510\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20210510\Symfony\Component\HttpKernel\Bundle\BundleInterface;
use RectorPrefix20210510\Symfony\Component\HttpKernel\Kernel;
use RectorPrefix20210510\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass;
final class AutowireArrayParameterHttpKernel extends \RectorPrefix20210510\Symfony\Component\HttpKernel\Kernel
{
    public function __construct()
    {
        // to invoke container override for test re-run
        parent::__construct('dev' . \random_int(0, 10000), \true);
    }
    public function registerContainerConfiguration(\RectorPrefix20210510\Symfony\Component\Config\Loader\LoaderInterface $loader) : void
    {
        $loader->load(__DIR__ . '/../config/autowire_array_parameter.php');
    }
    public function getCacheDir() : string
    {
        return \sys_get_temp_dir() . '/autowire_array_parameter_test';
    }
    public function getLogDir() : string
    {
        return \sys_get_temp_dir() . '/autowire_array_parameter_test_log';
    }
    /**
     * @return BundleInterface[]
     */
    public function registerBundles() : iterable
    {
        return [];
    }
    protected function build(\RectorPrefix20210510\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) : void
    {
        $containerBuilder->addCompilerPass(new \RectorPrefix20210510\Symplify\AutowireArrayParameter\DependencyInjection\CompilerPass\AutowireArrayParameterCompilerPass());
    }
}
