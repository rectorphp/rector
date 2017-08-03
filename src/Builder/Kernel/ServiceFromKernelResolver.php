<?php declare(strict_types=1);

namespace Rector\Builder\Kernel;

use Symfony\Component\HttpKernel\Kernel;

final class ServiceFromKernelResolver
{
    public function resolveServiceClassByNameFromKernel(string $serviceName, string $kernelClass): ?string
    {
        /** @var Kernel $kernel */
        $kernel = new $kernelClass('dev', true);
        $kernel->boot();

        // @todo: cache
        // @todo: initialize without creating cache or log directory
        // @todo: call only loadBundles() and initializeContainer() methods

        $container = $kernel->getContainer();
        if (! $container->has($serviceName)) {
            // service name could not be found
            return null;
        }

        $service = $container->get($serviceName);

        return get_class($service);
    }
}
