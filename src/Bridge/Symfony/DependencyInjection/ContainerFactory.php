<?php declare(strict_types=1);

namespace Rector\Bridge\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Kernel;

final class ContainerFactory
{
    /**
     * @var Container[]
     */
    private $containersByKernelClass = [];

    public function createFromKernelClass(string $kernelClass): Container
    {
        if ($this->containersByKernelClass[$kernelClass]) {
            return $this->containersByKernelClass[$kernelClass];
        }

        return $this->containersByKernelClass[$kernelClass] = $this->createContainerFromKernelClass($kernelClass);
    }

    private function createContainerFromKernelClass(string $kernelClass): Container
    {
        $kernel = $this->createKernelFromKernelClass($kernelClass);
        $kernel->boot();

        return $kernel->getContainer();
    }

    private function createKernelFromKernelClass(string $kernelClass): Kernel
    {
        $environment = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = (bool) ($options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true);

        return new $kernelClass($environment, $debug);
    }
}
