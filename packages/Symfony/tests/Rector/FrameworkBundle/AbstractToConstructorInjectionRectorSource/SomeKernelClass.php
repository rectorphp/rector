<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\FrameworkBundle\AbstractToConstructorInjectionRectorSource;

use Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeTranslator;
use Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeTranslatorInterface;
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
        $containerBuilder->register('stdClass', 'stdClass');
        $containerBuilder->setAlias('some_service', 'stdClass');

        $containerBuilder->register('translator.data_collector', SomeTranslator::class);
        $containerBuilder->setAlias('translator', 'translator.data_collector');
        $containerBuilder->setAlias(SomeTranslatorInterface::class, 'translator.data_collector');
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
