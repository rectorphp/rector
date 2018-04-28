<?php declare(strict_types=1);

namespace Rector\Bridge\Symfony;

use Rector\Configuration\Option;
use Rector\Contract\Bridge\ServiceTypeForNameProviderInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class DefaultServiceTypeForNameProvider implements ServiceTypeForNameProviderInterface
{
    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var SymfonyKernelParameterGuard
     */
    private $symfonyKernelParameterGuard;

    public function __construct(
        ParameterProvider $parameterProvider,
        SymfonyKernelParameterGuard $symfonyKernelParameterGuard
    ) {
        $this->parameterProvider = $parameterProvider;
        $this->symfonyKernelParameterGuard = $symfonyKernelParameterGuard;
    }

    public function provideTypeForName(string $name): ?string
    {
        $kernelClass = $this->parameterProvider->provideParameter(Option::KERNEL_CLASS_PARAMETER);
        $this->symfonyKernelParameterGuard->ensureKernelClassIsValid($kernelClass);

        // make this default, register and require kernel_class paramter, see:
        // https://github.com/rectorphp/rector/issues/428

        /** @var string $kernelClass */
        $container = $this->getContainerForKernelClass($kernelClass);

        if ($container->has($name)) {
            $definition = $container->get($name);

            return get_class($definition);
        }

        return null;
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

    private function getContainerForKernelClass(string $kernelClass): Container
    {
        if ($this->container) {
            return $this->container;
        }

        return $this->container = $this->createContainerFromKernelClass($kernelClass);
    }
}
