<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;

final class SomeKernelClass extends Kernel
{
    public function registerBundles(): iterable
    {
        return [];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
    }

    protected function build(ContainerBuilder $containerBuilder): void
    {
        $someServiceDefinition = $containerBuilder->register(ProductRepository::class);
        // so we can get it by the string name
        // @todo do this in own provider, not here, people will use private services
        $someServiceDefinition->setPublic(true);
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir() . '/_tmp';
    }

    public function getLogDir()
    {
        return sys_get_temp_dir() . '/_tmp';
    }
}
