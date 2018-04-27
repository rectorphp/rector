<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Symfony\FrameworkBundle\GetToConstructorInjectionRector\Source;

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
        $someServiceDefinition = $containerBuilder->register('some_service', 'stdClass');
        // so we can get it by the string name
        $someServiceDefinition->setPublic(true);
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir() . '/_tpm';
    }

    public function getLogDir()
    {
        return sys_get_temp_dir() . '/_tpm';
    }
}
