<?php declare(strict_types=1);

namespace Rector\Bridge\Symfony\DependencyInjection;

use Rector\DependencyInjection\CompilerPass\MakeServicesPublicCompilerPass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

final class ContainerFactory
{
    /**
     * @var Container[]
     */
    private $containersByKernelClass = [];

    public function createFromKernelClass(string $kernelClass): Container
    {
        if (isset($this->containersByKernelClass[$kernelClass])) {
            return $this->containersByKernelClass[$kernelClass];
        }

        return $this->containersByKernelClass[$kernelClass] = $this->createContainerFromKernelClass($kernelClass);
    }

    /**
     * Mimics https://github.com/symfony/symfony/blob/226e2f3949c5843b67826aca4839c2c6b95743cf/src/Symfony/Bundle/FrameworkBundle/Command/ContainerDebugCommand.php#L200-L203
     */
    private function createContainerFromKernelClass(string $kernelClass): Container
    {
        $kernel = $this->createKernelFromKernelClass($kernelClass);

        /** @var ContainerBuilder $containerBuilder */
        $containerBuilder = (new PrivatesCaller())->callPrivateMethod($kernel, 'buildContainer');
        $containerBuilder->getCompilerPassConfig()->addPass(new MakeServicesPublicCompilerPass());
        $containerBuilder->compile();

        return $containerBuilder;
    }

    private function createKernelFromKernelClass(string $kernelClass): Kernel
    {
        $environment = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = (bool) ($options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true);

        return new $kernelClass($environment, $debug);
    }
}
