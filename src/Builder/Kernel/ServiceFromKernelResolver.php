<?php declare(strict_types=1);

namespace Rector\Builder\Kernel;

use PhpParser\Node\Arg;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @see for inspiration https://github.com/sensiolabs-de/deprecation-detector/blob/master/src/TypeGuessing/Symfony/ContainerReader.phpě
 */
final class ServiceFromKernelResolver
{
    public function resolveServiceClassFromArgument(Arg $argNode, string $kernelClass): ?string
    {
        $serviceName = $argNode->value->value;
        $serviceType = $this->resolveServiceClassByNameFromKernel($serviceName, $kernelClass);

        if ($serviceType === null) {
            return null;
        }

        return $serviceType;
    }

    private function resolveServiceClassByNameFromKernel(string $serviceName, string $kernelClass): ?string
    {
        $container = $this->createContainerFromKernelClass($kernelClass);

        if (! $container->has($serviceName)) {
            // service name could not be found
            return null;
        }

        $service = $container->get($serviceName);

        return get_class($service);
    }

    private function createContainerFromKernelClass(string $kernelClass): ContainerInterface
    {
        $kernel = $this->createKernelFromKernelClass($kernelClass);

        return $kernel->getContainer();
    }

    private function createKernelFromKernelClass(string $kernelClass): Kernel
    {
        /** @var Kernel $kernel */
        $kernel = new $kernelClass('dev', true);
        $kernel->boot();

        return $kernel;

        // @todo: cache
        // @todo: initialize without creating cache or log directory
        // @todo: call only loadBundles() and initializeContainer() methods
    }
}
