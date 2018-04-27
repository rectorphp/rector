<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Symfony\FrameworkBundle\GetToConstructorInjectionRector\Source;

use Symfony\Component\Config\Loader\LoaderInterface;
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
}
