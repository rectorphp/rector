<?php declare(strict_types=1);

namespace Rector\Symfony\Bridge\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
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
     * Mimics https://github.com/symfony/symfony/blob/f834c9262b411aa5793fcea23694e3ad3b5acbb4/src/Symfony/Bundle/FrameworkBundle/Command/ContainerDebugCommand.php#L200-L203
     */
    private function createContainerFromKernelClass(string $kernelClass): Container
    {
        $kernel = $this->createKernelFromKernelClass($kernelClass);
        // preloads all the extensions and $containerBuilder->compile()
        $kernel->boot();

        /** @var ContainerBuilder $containerBuilder */
        $containerBuilder = (new PrivatesCaller())->callPrivateMethod($kernel, 'buildContainer');
        $containerBuilder->getCompilerPassConfig()->setRemovingPasses([]);

        // anonymous class on intention, since this depends on Symfony\DependencyInjection in rector-prefixed
        $containerBuilder->getCompilerPassConfig()->addPass(new class() implements CompilerPassInterface {
            public function process(ContainerBuilder $containerBuilder): void
            {
                foreach ($containerBuilder->getDefinitions() as $definition) {
                    $definition->setPublic(true);
                }
                foreach ($containerBuilder->getAliases() as $definition) {
                    $definition->setPublic(true);
                }
            }
        });

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
